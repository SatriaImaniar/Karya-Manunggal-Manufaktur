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

        // ─── Hapus jadwal Pending lama untuk mesin ini ────────────────────────
        // Sesuai logika TBM: data historis baru → recalculate → jadwal lama
        // yang belum dikerjakan (Pending) menjadi tidak relevan.
        // CATATAN: Completed dan In Progress TIDAK dihapus (riwayat pekerjaan).
        MaintenanceSchedule::where('machine_id', $machine->id)
            ->where('status', 'pending')
            ->delete();

        // ─── Generate jadwal looping 1 tahun ke depan ────────────────────────
        // Mulai dari next_schedule_date (period_end + tpm_interval),
        // lalu terus tambah tpm_interval sampai batas 1 tahun dari period_end.
        $intervalDays  = (int) ceil($result['tpm_interval']);
        $batasAkhir    = Carbon::parse($validated['period_end'])->addYear();
        $currentDate   = $result['next_schedule_date']->copy();
        $priority      = $this->determinePriority($result['mtbf']);
        $baseDesc      = "Preventive Maintenance — {$machine->name} | Tpm = {$result['tpm_interval']} hari | MTBF = {$result['mtbf']} jam";
        $scheduleCount = 0;

        while ($currentDate->lte($batasAkhir)) {
            MaintenanceSchedule::create([
                'machine_id'     => $machine->id,
                'history_id'     => $history->id,
                'scheduled_date' => $currentDate->toDateString(),
                'priority'       => $priority,
                'status'         => 'pending',
                'description'    => $baseDesc,
            ]);

            $scheduleCount++;
            $currentDate->addDays($intervalDays);
        }

        return redirect()
            ->route('admin.maintenance.history')
            ->with('success', sprintf(
                'Data berhasil disimpan. MTBF: %s jam | MTTR: %s jam | Availability: %s%% | Interval PM: %s hari. '
                . '%d jadwal PM dibuat (looping setiap %d hari selama 1 tahun ke depan).',
                $result['mtbf'],
                $result['mttr'],
                $result['availability'],
                $result['tpm_interval'],
                $scheduleCount,
                $intervalDays
            ));
    }

    /**
     * Hapus satu record data historis kerusakan.
     *
     * Logika penghapusan:
     *   1. Hapus semua jadwal PENDING yang berasal dari histori ini (history_id match)
     *   2. Hapus juga jadwal PENDING lain untuk mesin ini yang mungkin orphan
     *   3. Jangan hapus jadwal Completed / In Progress (riwayat pekerjaan teknisi)
     *   4. Hapus record historis itu sendiri
     */
    public function destroyHistory(MaintenanceHistory $history)
    {
        $machineName = $history->machine->name;
        $periode     = $history->period_start->format('d/m/Y') . ' – ' . $history->period_end->format('d/m/Y');

        // Hapus jadwal Pending yang berasal dari histori ini
        MaintenanceSchedule::where('history_id', $history->id)
            ->where('status', 'pending')
            ->delete();

        // Hapus histori
        $history->delete();

        return redirect()
            ->route('admin.maintenance.history')
            ->with('success', "Data historis {$machineName} periode {$periode} berhasil dihapus beserta jadwal Pending terkait.");
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
        $schedules   = MaintenanceSchedule::with([
                'machine.jenisKerusakans',
                'assignedTechnician',
                'history.jenisKerusakan',
            ])
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
                    'status'           => $schedule->status,
                    'priority'         => $schedule->priority,
                    'technician'       => optional($schedule->assignedTechnician)->name ?? 'Belum ditugaskan',
                    'completion_notes' => $schedule->completion_notes ?? '',
                    'completed_at'     => $schedule->completed_at
                                            ? $schedule->completed_at->format('d/m/Y H:i')
                                            : '',
                    'scheduled_date'   => $schedule->scheduled_date->format('d M Y'),
                    'machine_name'     => $schedule->machine->name,
                    'machine_code'     => $schedule->machine->code,
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
        $schedules = MaintenanceSchedule::with([
                'machine.jenisKerusakans',
                'history.jenisKerusakan',
            ])
            ->where('assigned_to', auth()->id())
            ->orderBy('scheduled_date')
            ->paginate(10);

        return view('teknisi.schedules.index', compact('schedules'));
    }

    /**
     * Update status jadwal oleh teknisi.
     *
     * Alur saat status → completed:
     *   1. Simpan completed_at dan completion_notes
     *   2. Ambil nilai Interval PM (Tpm) dari history terkait (atau history terbaru mesin)
     *   3. Konversi Tpm jam → hari: ceil(Tpm / operating_hours_per_day)
     *      → Default operasi pabrik = 8 jam/hari jika tidak terdefinisi
     *   4. Hitung tanggal berikutnya: completed_at + interval_hari
     *   5. Buat otomatis jadwal baru (status pending) jika belum ada jadwal aktif
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
            $updateData['completed_at']     = now();
            $updateData['completion_notes'] = $validated['completion_notes'] ?? null;
        }

        $schedule->update($updateData);

        // ─── Auto-generate jadwal PM berikutnya ───────────────────────────────
        // Spesifikasi TBM:
        //   tpm_interval yang tersimpan di DB sudah dalam satuan HARI.
        //   (Nilai = k × MTBF, sudah dikonversi ke hari oleh TbmCalculatorService)
        //   Tidak perlu dibagi operating_hours_per_day.
        //   next_date = completed_at + tpm_interval_hari
        // ─────────────────────────────────────────────────────────────────────
        $autoScheduleMessage = '';

        if ($validated['status'] === 'completed') {
            // 1. Muat relasi mesin
            $schedule->load(['machine', 'history']);

            // 2. Ambil nilai Tpm (HARI) — dari history jadwal ini,
            //    atau fallback ke history terbaru mesin jika history_id kosong
            $tpmDays = null;

            if ($schedule->history && $schedule->history->tpm_interval) {
                // Sumber utama: history yang menghasilkan jadwal ini
                $tpmDays = (float) $schedule->history->tpm_interval;
            } else {
                // Fallback: ambil history terbaru mesin yang punya tpm_interval
                $latestHistory = $schedule->machine
                    ->maintenanceHistories()
                    ->whereNotNull('tpm_interval')
                    ->orderByDesc('period_end')
                    ->first();

                if ($latestHistory) {
                    $tpmDays = (float) $latestHistory->tpm_interval;
                }
            }

            // 3. tpm_interval sudah dalam HARI — langsung pakai sebagai interval
            //    Fallback 30 hari jika tidak ada data Tpm sama sekali
            if ($tpmDays && $tpmDays > 0) {
                $daysInterval = (int) ceil($tpmDays);
            } else {
                $daysInterval = 30; // fallback standar
            }

            // 4. Tanggal jadwal berikutnya dihitung dari waktu selesai aktual
            $completedAt = $schedule->fresh()->completed_at;
            $nextDate    = $completedAt->copy()->addDays($daysInterval);

            // 5. Cek apakah sudah ada jadwal aktif (pending/in_progress) untuk mesin ini
            //    agar tidak terjadi duplikasi jadwal
            $alreadyExists = MaintenanceSchedule::where('machine_id', $schedule->machine_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->where('scheduled_date', '>=', now()->toDateString())
                ->exists();

            if (!$alreadyExists) {
                // Bersihkan suffix [Auto-Rekur] lama agar tidak menumpuk
                $baseDesc = preg_replace('/\s*\[Auto-Rekur\]/', '', $schedule->description ?? '');

                MaintenanceSchedule::create([
                    'machine_id'     => $schedule->machine_id,
                    'history_id'     => $schedule->history_id,
                    'scheduled_date' => $nextDate,
                    'priority'       => $schedule->priority,
                    'status'         => 'pending',
                    'description'    => trim($baseDesc) . ' [Auto-Rekur]',
                ]);

                $autoScheduleMessage = sprintf(
                    ' Jadwal PM berikutnya otomatis dibuat untuk tanggal %s (Interval Tpm: %s hari).',
                    $nextDate->format('d/m/Y'),
                    $tpmDays ? number_format($tpmDays, 2) : $daysInterval
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $statusLabel = $validated['status'] === 'completed' ? 'Selesai' : 'Dalam Pengerjaan';

        return redirect()
            ->route('teknisi.schedules')
            ->with('success', "Status jadwal berhasil diubah ke: {$statusLabel}.{$autoScheduleMessage}");
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
