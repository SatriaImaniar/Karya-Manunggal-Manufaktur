@extends('layouts.app')
@section('title', 'Kalkulasi TBM — ' . $machine->name)
@section('page-title', 'Detail Kalkulasi TBM')

@section('content')
{{-- Machine Info Header --}}
<div class="card-custom mb-4 animate-in">
    <div class="card-body-custom">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="stat-icon" style="background: var(--gradient-primary); width:56px; height:56px; font-size:1.5rem; border-radius:14px">
                    <i class="bi bi-cpu"></i>
                </div>
            </div>
            <div class="col">
                <h5 class="fw-bold mb-1">{{ $machine->name }}</h5>
                <div class="d-flex flex-wrap gap-3" style="font-size:.85rem;color:#64748b">
                    <span><i class="bi bi-tag me-1"></i>{{ $machine->code }}</span>
                    <span><i class="bi bi-gear me-1"></i>{{ $machine->type ?? '-' }}</span>
                    <span><i class="bi bi-geo-alt me-1"></i>{{ $machine->location ?? '-' }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $machine->operating_hours_per_day }} jam/hari</span>
                    <span class="badge bg-{{ $machine->status_badge }}">{{ ucfirst($machine->status) }}</span>
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.maintenance.history') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Histories Table --}}
@foreach($histories as $index => $history)
<div class="card-custom mb-4 animate-in">
    <div class="card-header-custom">
        <h6>
            <i class="bi bi-clock-history me-2"></i>
            Periode: {{ $history->period_start->format('d/m/Y') }} — {{ $history->period_end->format('d/m/Y') }}
        </h6>
        <span class="badge bg-secondary">Record #{{ $index + 1 }}</span>
    </div>
    <div class="card-body-custom">
        {{-- Input Values --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="text-muted" style="font-size:.75rem;font-weight:600">TOTAL WAKTU OPERASI (T)</div>
                    <div class="fw-bold fs-4 text-dark">{{ number_format($history->total_operating_time, 1) }}</div>
                    <div class="text-muted" style="font-size:.75rem">jam</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="text-muted" style="font-size:.75rem;font-weight:600">JUMLAH KERUSAKAN (N)</div>
                    <div class="fw-bold fs-4 text-dark">{{ $history->failure_count }}</div>
                    <div class="text-muted" style="font-size:.75rem">kali</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="text-muted" style="font-size:.75rem;font-weight:600">TOTAL WAKTU PERBAIKAN (Tr)</div>
                    <div class="fw-bold fs-4 text-dark">{{ number_format($history->total_repair_time, 1) }}</div>
                    <div class="text-muted" style="font-size:.75rem">jam</div>
                </div>
            </div>
        </div>

        {{-- Calculation Results --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center text-white" style="background: var(--gradient-primary)">
                    <div style="font-size:.7rem;font-weight:600;opacity:.8">MTBF = T / N</div>
                    <div class="fw-bold fs-3">{{ $history->mtbf }}</div>
                    <div style="font-size:.75rem;opacity:.8">jam antar kerusakan</div>
                    <div class="mt-1" style="font-size:.7rem;opacity:.6">
                        {{ number_format($history->total_operating_time, 1) }} / {{ $history->failure_count }} = {{ $history->mtbf }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center text-white" style="background: var(--gradient-warning)">
                    <div style="font-size:.7rem;font-weight:600;opacity:.8">MTTR = Tr / N</div>
                    <div class="fw-bold fs-3">{{ $history->mttr }}</div>
                    <div style="font-size:.75rem;opacity:.8">jam per perbaikan</div>
                    <div class="mt-1" style="font-size:.7rem;opacity:.6">
                        {{ number_format($history->total_repair_time, 1) }} / {{ $history->failure_count }} = {{ $history->mttr }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 text-center text-white" style="background: var(--gradient-success)">
                    <div style="font-size:.7rem;font-weight:600;opacity:.8">Tpm = k × MTBF</div>
                    <div class="fw-bold fs-3">{{ $history->tpm_interval }}</div>
                    <div style="font-size:.75rem;opacity:.8">jam interval PM</div>
                    <div class="mt-1" style="font-size:.7rem;opacity:.6">
                        0.7 × {{ $history->mtbf }} = {{ $history->tpm_interval }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Schedules --}}
        @if($history->schedules->count() > 0)
            <h6 class="fw-bold mt-4 mb-3" style="font-size:.85rem">
                <i class="bi bi-calendar-check me-1"></i> Jadwal Maintenance dari Kalkulasi Ini
            </h6>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal Jadwal</th>
                            <th>Prioritas</th>
                            <th>Teknisi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history->schedules as $schedule)
                            <tr>
                                <td class="fw-semibold">{{ $schedule->scheduled_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $schedule->priority_badge }}">
                                        {{ ucfirst($schedule->priority) }}
                                    </span>
                                </td>
                                <td>{{ $schedule->assignedTechnician?->name ?? 'Belum ditugaskan' }}</td>
                                <td>
                                    <span class="badge bg-{{ $schedule->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($history->notes)
            <div class="mt-3 p-2 rounded" style="background:#f8fafc;font-size:.85rem">
                <i class="bi bi-sticky me-1 text-muted"></i>
                <strong>Catatan:</strong> {{ $history->notes }}
            </div>
        @endif
    </div>
</div>
@endforeach

@if($histories->isEmpty())
<div class="card-custom animate-in">
    <div class="card-body-custom text-center py-5 text-muted">
        <i class="bi bi-inbox" style="font-size:3rem"></i>
        <p class="mt-3 mb-0">Belum ada data historis untuk mesin ini.</p>
        <a href="{{ route('admin.maintenance.history') }}" class="btn btn-gradient-primary mt-3">
            <i class="bi bi-plus me-1"></i> Input Data Historis
        </a>
    </div>
</div>
@endif
@endsection
