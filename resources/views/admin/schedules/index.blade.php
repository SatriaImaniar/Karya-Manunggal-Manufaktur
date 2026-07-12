@extends('layouts.app')
@section('title', 'Jadwal Maintenance')
@section('page-title', 'Jadwal Preventive Maintenance')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="row g-4">
        {{-- Calendar --}}
        <div class="col-lg-8 animate-in">
            <div class="card-custom">
                <div class="card-header-custom">
                    <h6><i class="bi bi-calendar3 me-2"></i>Kalender Jadwal</h6>
                </div>
                <div class="card-body-custom">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        {{-- Schedule List --}}
        <div class="col-lg-4 animate-in">
            <div class="card-custom">
                <div class="card-header-custom">
                    <h6><i class="bi bi-list-check me-2"></i>Daftar Jadwal</h6>
                </div>
                <div class="card-body-custom p-0" style="max-height:680px;overflow-y:auto">
                    @forelse($schedules as $schedule)
                        <div class="p-3 border-bottom" style="border-color:#f1f5f9 !important">
                            {{-- Header: Nama Mesin + Badge Status --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold" style="font-size:.9rem">{{ $schedule->machine->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $schedule->machine->code }}</div>
                                    {{-- Jenis Kerusakan: dari history spesifik, atau fallback ke master mesin --}}
                                    @php
                                        $jenisKerusakanTampil = collect();
                                        if ($schedule->history && $schedule->history->jenisKerusakan)
                                            $jenisKerusakanTampil->push($schedule->history->jenisKerusakan);
                                        elseif ($schedule->machine->jenisKerusakans->isNotEmpty())
                                            $jenisKerusakanTampil = $schedule->machine->jenisKerusakans->take(3);
                                    @endphp
                                    @if($jenisKerusakanTampil->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($jenisKerusakanTampil as $jk)
                                                <span class="badge rounded-pill"
                                                    style="font-size:.62rem;background:#fee2e2;color:#991b1b;border:1px solid #fecaca"
                                                    title="{{ $jk->deskripsi ?? '' }}">
                                                    <i class="bi bi-wrench-adjustable me-1"></i>{{ $jk->nama_kerusakan }}
                                                </span>
                                            @endforeach
                                            @if($schedule->machine->jenisKerusakans->count() > 3 && $jenisKerusakanTampil->count() === 3)
                                                <span class="badge rounded-pill"
                                                    style="font-size:.62rem;background:#e2e8f0;color:#475569">
                                                    +{{ $schedule->machine->jenisKerusakans->count() - 3 }} lagi
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <span class="badge bg-{{ $schedule->status_badge }}" style="font-size:.7rem">
                                        {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                    </span>
                                    @if(str_contains($schedule->description ?? '', '[Auto-Rekur]'))
                                        <span class="badge bg-secondary" style="font-size:.65rem">
                                            <i class="bi bi-arrow-repeat me-1"></i>Auto-Rekur
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Info Tanggal & Prioritas --}}
                            <div class="d-flex gap-2 mb-2" style="font-size:.8rem">
                                <span class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>{{ $schedule->scheduled_date->format('d M Y') }}
                                </span>
                                <span class="badge bg-{{ $schedule->priority_badge }}" style="font-size:.65rem">
                                    {{ ucfirst($schedule->priority) }}
                                </span>
                            </div>

                            {{-- Catatan Teknisi (tampil jika completed dan ada catatan) --}}
                            @if($schedule->status === 'completed')
                                <div class="mb-2 p-2 rounded-2" style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:.78rem">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <strong class="text-success">Selesai</strong>
                                        <span class="text-muted ms-auto">{{ $schedule->completed_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($schedule->completion_notes)
                                        <div class="d-flex gap-1 align-items-start mt-1">
                                            <i class="bi bi-sticky-fill text-success mt-1" style="flex-shrink:0"></i>
                                            <span class="text-muted" style="line-height:1.4">
                                                {{ Str::limit($schedule->completion_notes, 80) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Tidak ada catatan dari teknisi.</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Assign Technician (hanya jika belum selesai) --}}
                            @if($schedule->status !== 'completed')
                                <form action="{{ route('admin.schedules.assign', $schedule) }}" method="POST"
                                    class="d-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="assigned_to" class="form-select form-select-sm" style="font-size:.8rem">
                                        <option value="">Pilih Teknisi</option>
                                        @foreach ($teknisiList as $teknisi)
                                            <option value="{{ $teknisi->id }}"
                                                {{ $schedule->assigned_to == $teknisi->id ? 'selected' : '' }}>
                                                {{ $teknisi->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-gradient-primary" style="flex-shrink:0">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </form>
                            @else
                                {{-- Teknisi yang mengerjakan --}}
                                @if($schedule->assignedTechnician)
                                    <div class="d-flex align-items-center gap-1 mt-1" style="font-size:.78rem;color:#64748b">
                                        <i class="bi bi-person-fill"></i>
                                        <span>{{ $schedule->assignedTechnician->name }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-x" style="font-size:2rem"></i>
                            <p class="mt-2 mb-0">Belum ada jadwal</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- ===================================================================
     MODAL DETAIL JADWAL (untuk eventClick FullCalendar)
     Diletakkan di luar section content agar tidak terganggu stacking
     =================================================================== --}}
<div class="modal fade" id="scheduleDetailModal" tabindex="-1"
     aria-labelledby="scheduleDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="scheduleDetailModalHeader">
                <h6 class="modal-title fw-bold" id="scheduleDetailModalLabel">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span id="modal-machine-name">Detail Jadwal</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                {{-- Info Mesin --}}
                <div class="mb-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="fw-semibold mb-1" id="modal-machine-full"></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span id="modal-status-badge" class="badge"></span>
                        <span id="modal-priority-badge" class="badge"></span>
                    </div>
                </div>

                {{-- Info Jadwal --}}
                <div class="mb-3">
                    <div class="row g-2" style="font-size:.875rem">
                        <div class="col-5 text-muted">Tanggal Jadwal</div>
                        <div class="col-7 fw-semibold" id="modal-scheduled-date">—</div>
                        <div class="col-5 text-muted">Teknisi</div>
                        <div class="col-7" id="modal-technician">—</div>
                    </div>
                </div>

                {{-- Info Selesai (muncul hanya jika completed) --}}
                <div id="modal-completion-section" class="d-none">
                    <hr class="my-2">
                    <div class="row g-2 mb-2" style="font-size:.875rem">
                        <div class="col-5 text-muted">Diselesaikan</div>
                        <div class="col-7 fw-semibold text-success" id="modal-completed-at">—</div>
                    </div>
                    <div id="modal-notes-section" class="d-none">
                        <div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:.85rem">
                            <div class="d-flex align-items-center gap-1 mb-2">
                                <i class="bi bi-sticky-fill text-success"></i>
                                <strong class="text-success">Catatan Penyelesaian Teknisi</strong>
                            </div>
                            <p class="mb-0 text-muted" id="modal-completion-notes" style="line-height:1.6"></p>
                        </div>
                    </div>
                    <div id="modal-no-notes-section" class="d-none">
                        <p class="text-muted fst-italic" style="font-size:.85rem">
                            <i class="bi bi-info-circle me-1"></i>Teknisi tidak mengisi catatan penyelesaian.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const events = @json($formattedEvents);

            // Mapping status → label Indonesia
            const statusLabel = {
                pending:     'Pending',
                in_progress: 'Dalam Pengerjaan',
                completed:   'Selesai',
                overdue:     'Overdue',
            };

            // Mapping priority → label
            const priorityLabel = {
                low:      'Low',
                medium:   'Medium',
                high:     'High',
                critical: 'Critical',
            };

            // Mapping status → warna badge Bootstrap
            const statusBgClass = {
                pending:     'bg-warning text-dark',
                in_progress: 'bg-info text-white',
                completed:   'bg-success text-white',
                overdue:     'bg-danger text-white',
            };

            const priorityBgClass = {
                low:      'bg-secondary text-white',
                medium:   'bg-primary text-white',
                high:     'bg-warning text-dark',
                critical: 'bg-danger text-white',
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'dayGridMonth,dayGridWeek,listWeek'
                },
                events: events,
                eventDisplay: 'block',
                height: 'auto',
                locale: 'id',

                eventDidMount: function (info) {
                    info.el.style.borderRadius  = '6px';
                    info.el.style.padding       = '2px 6px';
                    info.el.style.fontSize      = '0.75rem';
                    info.el.style.fontWeight    = '600';
                    info.el.style.cursor        = 'pointer';
                },

                // Saat event di kalender diklik → isi modal dan tampilkan
                eventClick: function (info) {
                    const props = info.event.extendedProps;
                    const status   = props.status   || 'pending';
                    const priority = props.priority || 'medium';

                    // Header modal
                    document.getElementById('modal-machine-name').textContent =
                        props.machine_name || info.event.title;

                    // Info mesin
                    document.getElementById('modal-machine-full').textContent =
                        (props.machine_code ? props.machine_code + ' — ' : '') +
                        (props.machine_name || info.event.title);

                    // Badge status & priority
                    const statusBadge   = document.getElementById('modal-status-badge');
                    const priorityBadge = document.getElementById('modal-priority-badge');
                    statusBadge.className   = 'badge ' + (statusBgClass[status]   || 'bg-secondary text-white');
                    statusBadge.textContent  = statusLabel[status]   || status;
                    priorityBadge.className  = 'badge ' + (priorityBgClass[priority] || 'bg-secondary text-white');
                    priorityBadge.textContent = priorityLabel[priority] || priority;

                    // Tanggal & teknisi
                    document.getElementById('modal-scheduled-date').textContent =
                        props.scheduled_date || info.event.startStr;
                    document.getElementById('modal-technician').textContent =
                        props.technician || 'Belum ditugaskan';

                    // Section selesai
                    const completionSection = document.getElementById('modal-completion-section');
                    const notesSection      = document.getElementById('modal-notes-section');
                    const noNotesSection    = document.getElementById('modal-no-notes-section');

                    if (status === 'completed') {
                        completionSection.classList.remove('d-none');
                        document.getElementById('modal-completed-at').textContent =
                            props.completed_at || '—';

                        if (props.completion_notes && props.completion_notes.trim() !== '') {
                            notesSection.classList.remove('d-none');
                            noNotesSection.classList.add('d-none');
                            document.getElementById('modal-completion-notes').textContent =
                                props.completion_notes;
                        } else {
                            notesSection.classList.add('d-none');
                            noNotesSection.classList.remove('d-none');
                        }
                    } else {
                        completionSection.classList.add('d-none');
                        notesSection.classList.add('d-none');
                        noNotesSection.classList.add('d-none');
                    }

                    // Tampilkan modal
                    const modal = new bootstrap.Modal(
                        document.getElementById('scheduleDetailModal')
                    );
                    modal.show();
                }
            });

            calendar.render();
        });
    </script>
@endpush

