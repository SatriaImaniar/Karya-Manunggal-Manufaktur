@extends('layouts.app')
@section('title', 'Dashboard Teknisi')
@section('page-title', 'Dashboard Teknisi')

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-warning)">
                    <i class="bi bi-clock"></i>
                </div>
                <span class="badge bg-warning-subtle text-warning">Pending</span>
            </div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-label">Jadwal Menunggu</div>
        </div>
    </div>
    <div class="col-md-4 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-info)">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <span class="badge bg-info-subtle text-info">In Progress</span>
            </div>
            <div class="stat-value">{{ $inProgressCount }}</div>
            <div class="stat-label">Dalam Pengerjaan</div>
        </div>
    </div>
    <div class="col-md-4 animate-in">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background: var(--gradient-success)">
                    <i class="bi bi-check2-all"></i>
                </div>
                <span class="badge bg-success-subtle text-success">Selesai</span>
            </div>
            <div class="stat-value">{{ $completedCount }}</div>
            <div class="stat-label">Sudah Selesai</div>
        </div>
    </div>
</div>

{{-- Upcoming Schedules --}}
<div class="card-custom animate-in">
    <div class="card-header-custom">
        <h6><i class="bi bi-calendar-event me-2"></i>Jadwal Yang Harus Dikerjakan</h6>
        <a href="{{ route('teknisi.schedules') }}" class="btn btn-sm btn-gradient-primary">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body-custom p-0">
        @if($upcomingSchedules->count() > 0)
            @foreach($upcomingSchedules as $schedule)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom" style="border-color:#f1f5f9 !important">
                    <div class="stat-icon" style="background: var(--gradient-primary); width:44px; height:44px; border-radius:10px; font-size:1rem; flex-shrink:0">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:.9rem">{{ $schedule->machine->name }}</div>
                        <div class="d-flex gap-2 align-items-center mt-1" style="font-size:.8rem">
                            <span class="text-muted">
                                <i class="bi bi-calendar me-1"></i>{{ $schedule->scheduled_date->format('d M Y') }}
                            </span>
                            <span class="badge bg-{{ $schedule->priority_badge }}" style="font-size:.65rem">
                                {{ ucfirst($schedule->priority) }}
                            </span>
                        </div>
                    </div>
                    <span class="badge bg-{{ $schedule->status_badge }}">
                        {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                    </span>
                </div>
            @endforeach
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle" style="font-size:2.5rem;color:#10b981"></i>
                <p class="mt-2 mb-0 fw-semibold">Semua pekerjaan sudah selesai! 🎉</p>
            </div>
        @endif
    </div>
</div>
@endsection
