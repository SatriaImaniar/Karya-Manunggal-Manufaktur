@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-primary)">
                    <i class="bi bi-cpu"></i>
                </div>
                <span class="badge bg-primary-subtle text-primary">Total</span>
            </div>
            <div class="stat-value">{{ $totalMachines }}</div>
            <div class="stat-label">Total Mesin</div>
            <div class="mt-2" style="font-size:.75rem;color:#10b981">
                <i class="bi bi-check-circle"></i> {{ $activeMachines }} aktif
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-warning)">
                    <i class="bi bi-clock"></i>
                </div>
                <span class="badge bg-warning-subtle text-warning">Menunggu</span>
            </div>
            <div class="stat-value">{{ $pendingSchedules }}</div>
            <div class="stat-label">Jadwal Pending</div>
            <div class="mt-2" style="font-size:.75rem;color:#f59e0b">
                <i class="bi bi-arrow-repeat"></i> {{ $inProgressSchedules }} dalam proses
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-success)">
                    <i class="bi bi-check2-all"></i>
                </div>
                <span class="badge bg-success-subtle text-success">Selesai</span>
            </div>
            <div class="stat-value">{{ $completedSchedules }}</div>
            <div class="stat-label">Jadwal Selesai</div>
            <div class="mt-2" style="font-size:.75rem;color:#64748b">
                dari {{ $totalSchedules }} total jadwal
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-danger)">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <span class="badge bg-danger-subtle text-danger">Alert</span>
            </div>
            <div class="stat-value">{{ $overdueSchedules }}</div>
            <div class="stat-label">Jadwal Overdue</div>
            <div class="mt-2" style="font-size:.75rem;color:#ef4444">
                <i class="bi bi-exclamation-circle"></i> perlu perhatian
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Upcoming Schedules --}}
    <div class="col-lg-7 animate-in">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6><i class="bi bi-calendar-event me-2"></i>Jadwal 7 Hari Ke Depan</h6>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-gradient-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body-custom p-0">
                @if($upcomingSchedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Mesin</th>
                                    <th>Tanggal</th>
                                    <th>Teknisi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingSchedules as $schedule)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $schedule->machine->name }}</div>
                                            <div class="text-muted" style="font-size:.75rem">{{ $schedule->machine->code }}</div>
                                        </td>
                                        <td>{{ $schedule->scheduled_date->format('d M Y') }}</td>
                                        <td>{{ $schedule->assignedTechnician?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-status bg-{{ $schedule->status_badge }}">
                                                {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check" style="font-size:2rem"></i>
                        <p class="mt-2 mb-0">Tidak ada jadwal dalam 7 hari ke depan</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Histories --}}
    <div class="col-lg-5 animate-in">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6><i class="bi bi-clock-history me-2"></i>Histori Terbaru</h6>
                <a href="{{ route('admin.maintenance.history') }}" class="btn btn-sm btn-outline-secondary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body-custom p-0">
                @if($recentHistories->count() > 0)
                    @foreach($recentHistories as $history)
                        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="border-color:#f1f5f9 !important">
                            <div class="stat-icon" style="background: var(--gradient-info); width:40px; height:40px; border-radius:10px; font-size:.9rem; flex-shrink:0">
                                <i class="bi bi-cpu"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:.85rem">{{ $history->machine->name }}</div>
                                <div class="text-muted" style="font-size:.7rem">
                                    MTBF: {{ $history->mtbf }} hari &middot; MTTR: {{ $history->mttr }} jam &middot; Tpm: {{ $history->tpm_interval }} hari
                                </div>
                            </div>
                            <a href="{{ route('admin.maintenance.calculation', $history->machine) }}"
                               class="btn btn-sm btn-light" title="Detail">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size:2rem"></i>
                        <p class="mt-2 mb-0">Belum ada data historis</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
