@extends('layouts.app')
@section('title', 'Jadwal Saya')
@section('page-title', 'Jadwal Preventive Maintenance Saya')

@section('content')
<div class="card-custom animate-in">
    <div class="card-header-custom">
        <h6><i class="bi bi-list-check me-2"></i>Jadwal yang Ditugaskan</h6>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Mesin</th>
                        <th>Tanggal Jadwal</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $schedule->machine->name }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $schedule->machine->code }}</div>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $schedule->scheduled_date->format('d M Y') }}</span>
                                @if($schedule->scheduled_date->isPast() && $schedule->status !== 'completed')
                                    <br><span class="text-danger" style="font-size:.75rem">
                                        <i class="bi bi-exclamation-circle"></i> Overdue
                                    </span>
                                @elseif($schedule->scheduled_date->isToday())
                                    <br><span class="text-warning" style="font-size:.75rem">
                                        <i class="bi bi-clock"></i> Hari ini
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $schedule->priority_badge }}">
                                    {{ ucfirst($schedule->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $schedule->status_badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                </span>
                            </td>
                            <td style="max-width:200px">
                                <span style="font-size:.85rem">{{ Str::limit($schedule->description, 60) }}</span>
                            </td>
                            <td class="text-center">
                                @if($schedule->status === 'pending')
                                    <form action="{{ route('teknisi.schedules.update-status', $schedule) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="btn btn-sm btn-info text-white"
                                                title="Mulai Kerjakan"
                                                onclick="return confirm('Mulai mengerjakan maintenance ini?')">
                                            <i class="bi bi-play-fill me-1"></i>Mulai
                                        </button>
                                    </form>
                                @elseif($schedule->status === 'in_progress')
                                    <button type="button" class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#completeModal{{ $schedule->id }}">
                                        <i class="bi bi-check-lg me-1"></i>Selesai
                                    </button>
                                @elseif($schedule->status === 'completed')
                                    <span class="text-success" style="font-size:.85rem">
                                        <i class="bi bi-check-circle-fill"></i>
                                        {{ $schedule->completed_at?->format('d/m/Y H:i') }}
                                    </span>
                                    @if($schedule->completion_notes)
                                        <div class="text-muted mt-1" style="font-size:.75rem" title="{{ $schedule->completion_notes }}">
                                            <i class="bi bi-sticky"></i> {{ Str::limit($schedule->completion_notes, 30) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size:2rem"></i>
                                <p class="mt-2 mb-0">Belum ada jadwal yang ditugaskan kepada Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $schedules->links() }}
</div>

{{-- ======================================================
     SEMUA MODAL DIPINDAHKAN KE SINI (di luar tabel)
     Alasan: Modal di dalam <td>/<tbody> dapat menyebabkan
     stacking context CSS tabel memblokir interaksi input.
     ====================================================== --}}
@foreach($schedules as $schedule)
    @if($schedule->status === 'in_progress')
        <div class="modal fade" id="completeModal{{ $schedule->id }}" tabindex="-1"
             aria-labelledby="completeModalLabel{{ $schedule->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('teknisi.schedules.update-status', $schedule) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">

                        <div class="modal-header">
                            <h6 class="modal-title fw-bold" id="completeModalLabel{{ $schedule->id }}">
                                <i class="bi bi-check-circle me-2 text-success"></i>Konfirmasi Selesai
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                Maintenance untuk <strong>{{ $schedule->machine->name }}</strong>
                                ({{ $schedule->machine->code }})
                            </p>
                            <div class="mb-3">
                                <label for="completion_notes_{{ $schedule->id }}" class="form-label fw-semibold">
                                    Catatan Penyelesaian
                                </label>
                                {{-- ID dibuat unik per jadwal agar tidak ada duplikasi ID di DOM --}}
                                <textarea class="form-control"
                                          name="completion_notes"
                                          id="completion_notes_{{ $schedule->id }}"
                                          rows="4"
                                          placeholder="Tuliskan catatan pekerjaan yang sudah dilakukan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>Tandai Selesai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

@push('scripts')
<script>
    // Auto-focus textarea setiap kali modal ditampilkan
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[id^="completeModal"]').forEach(function (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function () {
                var textarea = modalEl.querySelector('textarea[name="completion_notes"]');
                if (textarea) {
                    textarea.focus();
                }
            });
        });
    });
</script>
@endpush
