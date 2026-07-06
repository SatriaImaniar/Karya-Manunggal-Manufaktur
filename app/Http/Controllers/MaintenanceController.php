<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MaintenanceHistory;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\JenisKerusakan;
use App\Services\TbmCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * MaintenanceController
 *
 * Mengelola seluruh alur kerja sistem TBM:
 *   1. Input data historis kerusakan mesin (T, Tr, N)
 *   2. Kalkulasi otomatis MTBF, MTTR, Availability, Tpm
 *   3. Generate jadwal preventive maintenance
 *   4. Rekap & export data
 *   5. Dashboard untuk Admin/SPV dan Teknisi
 */
class MaintenanceController extends Controller
{
    /**
     * Inject TbmCalculatorService via constructor.
     */
    public function __construct(
        protected TbmCalculatorService $calculator
    ) {}

    // =========================================================================
    // ADMIN: DASHBOARD
    // =========================================================================

    /**
     * Dashboard utama Admin/SPV.
     * Menampilkan ringkasan jumlah mesin, jadwal, dan status terkini.
     */
    public function dashboard()
    {
        $totalMachines   = Machine::count();
        $activeMachines  = Machine::where('status', 'active')->count();

        $totalSchedules     = MaintenanceSchedule::count();
        $pendingSchedules   = MaintenanceSchedule::where('status', 'pending')->count();
        $inProgressSchedules = MaintenanceSchedule::where('status', 'in_progress')->count();
        $completedSchedules = MaintenanceSchedule::where('status', 'completed')->count();
        $overdueSchedules   = MaintenanceSchedule::where('status', 'overdue')->count();

        // Jadwal mendatang dalam 7 hari ke depan
        $upcomingSchedules = MaintenanceSchedule::with(['machine', 'assignedTechnician'])
            ->where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->where('status', '!=', 'completed')
            ->orderBy('scheduled_date')
            ->get();

        // 5 histori kerusakan terbaru
        $recentHistories = MaintenanceHistory::with('machine')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMachines',
            'activeMachines',
            'totalSchedules',
            'pendingSchedules',
            'inProgressSchedules',
            'completedSchedules',
            'overdueSchedules',
            'upcomingSchedules',
            'recentHistories'
        ));
    }

    // =========================================================================
    // ADMIN: DATA HISTORIS & KALKULASI TBM
    // =========================================================================

    /**
     * Tampilkan halaman input & daftar data historis kerusakan.
     */
    public function historyIndex()
    {
        $histories = MaintenanceHistory::with(['machine', 'jenisKerusakan', 'reporter'])
            ->latest()
            ->paginate(10);

        $machines = Machine::where('status', 'active')
            ->orderBy('name')
            ->get();

        $jenisKerusakanList = JenisKerusakan::orderBy('nama_kerusakan')->get();

        return view('admin.maintenance.history', compact(
            'histories',
            'machines',
            'jenisKerusakanList'
        ));
    }

    /**
     * Simpan data historis baru dan jalankan kalkulasi TBM otomatis.
     *
     * Alur:
     *   1. Validasi input (machine_id, T, N, Tr, periode, jenis_kerusakan)
     *   2. Hitung MTBF, MTTR, Availability, Tpm via TbmCalculatorService
     *   3. Simpan record ke maintenance_histories
     *   4. Generate jadwal maintenance baru ke maintenance_schedules
     */
    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'machine_id'          => 'required|exists:machines,id',
            'waktu_operasi_jam'   => 'required|numeric|min:0.01',
            'waktu_perbaikan_jam' => 'required|numeric|min:0',
            'jumlah_kerusakan'    => 'required|integer|min:1',
            'jenis_kerusakan_id'  => 'nullable|exists:jenis_kerusakan,id',
            'period_start'        => 'required|date',
            'period_end'          => 'required|date|after:period_start',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);

        // ─── Kalkulasi TBM ────────────────────────────────────────────────────
        // Input : T  = waktu_operasi_jam
        //         N  = jumlah_kerusakan
        //         Tr = waktu_perbaikan_jam
        //
        // Output: MTBF    = T / N
        //         MTTR    = Tr / N
        //         Avail.  = (MTBF / (MTBF + MTTR)) × 100%
        //         Tpm     = k × MTBF   (k = 0.075)
        //         Jadwal  = period_end + ceil(Tpm / jam_operasi_per_hari) hari
        // ─────────────────────────────────────────────────────────────────────
        $result = $this->calculator->calculateAll(
            totalOperatingTime: (float) $validated['waktu_operasi_jam'],
            failureCount: (int)   $validated['jumlah_kerusakan'],
            totalRepairTime: (float) $validated['waktu_perbaikan_jam'],
            periodEnd: Carbon::parse($validated['period_end']),
            operatingHoursPerDay: (float) $machine->operating_hours_per_day
        );

        // ─── Simpan histori ───────────────────────────────────────────────────
        $history = MaintenanceHistory::create([
            'machine_id'             => $machine->id,
            'reported_by'            => auth()->id(),
            'waktu_operasi_jam'      => $validated['waktu_operasi_jam'],
            'waktu_perbaikan_jam'    => $validated['waktu_perbaikan_jam'],
            'jumlah_kerusakan'       => $validated['jumlah_kerusakan'],
            'jenis_kerusakan_id'     => $validated['jenis_kerusakan_id'] ?? null,
            'period_start'           => $validated['period_start'],
            'period_end'             => $validated['period_end'],
            'mtbf'                   => $result['mtbf'],
            'mttr'                   => $result['mttr'],
            'availability_percentage' => $result['availability'],
            'tpm_interval'           => $result['tpm_interval'],
            'notes'                  => $validated['notes'] ?? null,
        ]);

        // ─── Generate jadwal preventive maintenance otomatis ──────────────────
        MaintenanceSchedule::create([
            'machine_id'    => $machine->id,
            'history_id'    => $history->id,
            'scheduled_date' => $result['next_schedule_date'],
            'priority'      => $this->determinePriority($result['mtbf']),
            'status'        => 'pending',
            'description'   => "Preventive Maintenance — {$machine->name} (TBM/Tpm = {$result['tpm_interval']} jam)",
        ]);

        return redirect()
            ->route('admin.maintenance.history')
            ->with('success', sprintf(
                'Data berhasil disimpan. MTBF: %s jam | MTTR: %s jam | Availability: %s%% | Tpm: %s jam. Jadwal dibuat untuk %s.',
                $result['mtbf'],
                $result['mttr'],
                $result['availability'],
                $result['tpm_interval'],
                $result['next_schedule_date']->format('d/m/Y')
            ));
    }

    /**
     * Tampilkan detail kalkulasi TBM untuk satu mesin.
     */
    public function showCalculation(Machine $machine)
    {
        $histories = $machine->maintenanceHistories()
            ->with(['jenisKerusakan', 'schedules.assignedTechnician'])
            ->latest()
            ->get();

        $schedules = $machine->maintenanceSchedules()
            ->with('assignedTechnician')
            ->orderBy('scheduled_date', 'desc')
            ->get();

        return view('admin.maintenance.calculation', compact('machine', 'histories', 'schedules'));
    }

    // =========================================================================
    // ADMIN: JADWAL MAINTENANCE
    // =========================================================================

    /**
     * Tampilkan semua jadwal maintenance.
     */
    public function scheduleIndex()
    {
        $schedules   = MaintenanceSchedule::with(['machine', 'assignedTechnician'])
            ->orderBy('scheduled_date')
            ->get();

        $formattedEvents = $schedules->map(function ($schedule) {
            $colors = [
                'pending'     => '#f59e0b',
                'in_progress' => '#06b6d4',
                'completed'   => '#10b981',
                'overdue'     => '#ef4444',
            ];

            return [
                'title' => $schedule->machine->code . ' - ' . $schedule->machine->name,
                'start' => $schedule->scheduled_date->format('Y-m-d'),
                'color' => $colors[$schedule->status] ?? '#64748b',
                'extendedProps' => [
                    'status' => $schedule->status,
                    'priority' => $schedule->priority,
                    'technician' => optional($schedule->assignedTechnician)->name ?? 'Belum ditugaskan',
                ],
            ];
        })->toArray();

        $teknisiList = User::where('role', 'teknisi')->get();
        $machines    = Machine::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'teknisiList', 'machines', 'formattedEvents'));
    }

    /**
     * Assign teknisi ke jadwal maintenance.
     */
    public function assignSchedule(Request $request, MaintenanceSchedule $schedule)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $schedule->update(['assigned_to' => $validated['assigned_to']]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Teknisi berhasil ditugaskan.');
    }

    // =========================================================================
    // TEKNISI: DASHBOARD & JADWAL
    // =========================================================================

    /**
     * Dashboard teknisi — ringkasan jadwal yang ditugaskan ke user ini.
     */
    public function teknisiDashboard()
    {
        $user = auth()->user();

        $pendingCount    = MaintenanceSchedule::where('assigned_to', $user->id)->where('status', 'pending')->count();
        $inProgressCount = MaintenanceSchedule::where('assigned_to', $user->id)->where('status', 'in_progress')->count();
        $completedCount  = MaintenanceSchedule::where('assigned_to', $user->id)->where('status', 'completed')->count();

        $upcomingSchedules = MaintenanceSchedule::with('machine')
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('scheduled_date')
            ->take(10)
            ->get();

        return view('teknisi.dashboard', compact(
            'pendingCount',
            'inProgressCount',
            'completedCount',
            'upcomingSchedules'
        ));
    }

    /**
     * Tampilkan jadwal yang ditugaskan ke teknisi saat ini.
     */
    public function teknisiSchedules()
    {
        $schedules = MaintenanceSchedule::with(['machine', 'history'])
            ->where('assigned_to', auth()->id())
            ->orderBy('scheduled_date')
            ->paginate(10);

        return view('teknisi.schedules.index', compact('schedules'));
    }

    /**
     * Update status jadwal oleh teknisi.
     */
    public function updateScheduleStatus(Request $request, MaintenanceSchedule $schedule)
    {
        if ($schedule->assigned_to !== auth()->id()) {
            abort(403, 'Anda tidak ditugaskan untuk jadwal ini.');
        }

        $validated = $request->validate([
            'status'           => 'required|in:in_progress,completed',
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $updateData['completed_at']       = now();
            $updateData['completion_notes']   = $validated['completion_notes'];
        }

        $schedule->update($updateData);

        $statusLabel = $validated['status'] === 'completed' ? 'Selesai' : 'Dalam Pengerjaan';

        return redirect()
            ->route('teknisi.schedules')
            ->with('success', "Status jadwal berhasil diubah ke: {$statusLabel}.");
    }

    // =========================================================================
    // ADMIN: REKAP & EXPORT DATA
    // =========================================================================

    /**
     * Tampilkan halaman rekap data lengkap.
     *
     * Menggabungkan data mesin, input operasi/kerusakan, dan hasil
     * kalkulasi TBM (MTBF, MTTR, Availability, Tpm interval).
     */
    public function reportIndex()
    {
        $histories = MaintenanceHistory::with(['machine', 'jenisKerusakan', 'reporter'])
            ->latest('period_end')
            ->paginate(15);

        // Statistik agregat untuk header rekap
        $stats = [
            'avg_mtbf'         => MaintenanceHistory::whereNotNull('mtbf')->avg('mtbf'),
            'avg_mttr'         => MaintenanceHistory::whereNotNull('mttr')->avg('mttr'),
            'avg_availability' => MaintenanceHistory::whereNotNull('availability_percentage')->avg('availability_percentage'),
            'total_records'    => MaintenanceHistory::count(),
        ];

        return view('admin.maintenance.report', compact('histories', 'stats'));
    }

    /**
     * Export data rekap ke JSON (siap untuk Excel/CSV).
     *
     * Menggabungkan: data mesin, waktu operasi, waktu perbaikan,
     * jumlah kerusakan, jenis kerusakan, MTBF, MTTR, Availability, Tpm.
     *
     * Filter opsional via query string: date_from, date_to, machine_id, jenis_kerusakan_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportData(Request $request)
    {
        $query = MaintenanceHistory::with(['machine', 'jenisKerusakan', 'reporter']);

        // ─── Filter opsional ──────────────────────────────────────────────────
        if ($request->filled('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('period_end', '<=', $request->date_to);
        }
        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }
        if ($request->filled('jenis_kerusakan_id')) {
            $query->where('jenis_kerusakan_id', $request->jenis_kerusakan_id);
        }

        $histories = $query->orderBy('period_end', 'desc')->get();

        // ─── Format kolom rekap sesuai urutan tabel di foto ──────────────────
        $exportData = $histories->map(fn($h) => [
            // Identifikasi mesin
            'No Mesin'              => $h->machine->code  ?? '-',
            'Nama Mesin'            => $h->machine->name  ?? '-',
            'Jenis Kerusakan'       => $h->jenisKerusakan?->nama_kerusakan ?? '-',
            // Periode
            'Periode Awal'          => $h->period_start->format('d/m/Y'),
            'Periode Akhir'         => $h->period_end->format('d/m/Y'),
            // Input data riil (T, N, Tr)
            'Waktu Operasi (T, jam)' => (float) $h->waktu_operasi_jam,
            'Jml Kerusakan (N)'     => $h->jumlah_kerusakan,
            'Waktu Perbaikan (Tr, jam)' => (float) $h->waktu_perbaikan_jam,
            // Hasil kalkulasi TBM
            'MTBF (jam)'            => (float) $h->mtbf,
            'MTTR (jam)'            => (float) $h->mttr,
            'Availability (%)'      => (float) $h->availability_percentage,
            'Interval Tpm (jam)'    => (float) $h->tpm_interval,
            // Meta
            'Dilaporkan Oleh'       => $h->reporter?->name ?? '-',
            'Catatan'               => $h->notes ?? '-',
        ])->toArray();

        return response()->json([
            'success'       => true,
            'total_records' => count($exportData),
            'data'          => $exportData,
        ]);
    }

    /**
     * Ringkasan statistik TBM per mesin (JSON).
     *
     * @param  Machine $machine
     * @return \Illuminate\Http\JsonResponse
     */
    public function machineSummary(Machine $machine)
    {
        $histories = $machine->maintenanceHistories()
            ->whereNotNull('mtbf')
            ->whereNotNull('mttr')
            ->whereNotNull('availability_percentage')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data historis untuk mesin ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'machine' => [
                    'code'     => $machine->code,
                    'name'     => $machine->name,
                    'type'     => $machine->type,
                    'location' => $machine->location,
                ],
                'statistics' => [
                    'total_records'    => $histories->count(),
                    'avg_mtbf'         => round($histories->avg('mtbf'), 2),
                    'min_mtbf'         => round($histories->min('mtbf'), 2),
                    'max_mtbf'         => round($histories->max('mtbf'), 2),
                    'avg_mttr'         => round($histories->avg('mttr'), 2),
                    'avg_availability' => round($histories->avg('availability_percentage'), 2),
                    'avg_tpm_interval' => round($histories->avg('tpm_interval'), 2),
                ],
                'latest_record' => [
                    'periode'      => $histories->first()->periode_label,
                    'mtbf'         => $histories->first()->mtbf,
                    'mttr'         => $histories->first()->mttr,
                    'availability' => $histories->first()->availability_percentage,
                    'tpm_interval' => $histories->first()->tpm_interval,
                ],
            ],
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Tentukan prioritas jadwal berdasarkan nilai MTBF.
     *
     * Semakin rendah MTBF → mesin sering rusak → prioritas lebih tinggi.
     *
     * Referensi dari data riil pabrik:
     *   MTBF < 200  jam → critical  (kerusakan sangat sering)
     *   MTBF < 500  jam → high      (Amada 60T: 422.55 → high)
     *   MTBF < 1000 jam → medium    (Konatsu: 603, Aida55T: 845, Aida60D: 704)
     *   MTBF ≥ 1000 jam → low       (Aida 60C: 1408.5 → low)
     *
     * @param  float  $mtbf
     * @return string 'critical'|'high'|'medium'|'low'
     */
    private function determinePriority(float $mtbf): string
    {
        return match (true) {
            $mtbf < 200  => 'critical',
            $mtbf < 500  => 'high',
            $mtbf < 1000 => 'medium',
            default      => 'low',
        };
    }
}
