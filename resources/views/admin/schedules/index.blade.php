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
                <div class="card-body-custom p-0" style="max-height:600px;overflow-y:auto">
                    @forelse($schedules as $schedule)
                        <div class="p-3 border-bottom" style="border-color:#f1f5f9 !important">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold" style="font-size:.9rem">{{ $schedule->machine->name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $schedule->machine->code }}</div>
                                </div>
                                <span class="badge bg-{{ $schedule->status_badge }}" style="font-size:.7rem">
                                    {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                </span>
                            </div>
                            <div class="d-flex gap-2 mb-2" style="font-size:.8rem">
                                <span class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>{{ $schedule->scheduled_date->format('d M Y') }}
                                </span>
                                <span class="badge bg-{{ $schedule->priority_badge }}" style="font-size:.65rem">
                                    {{ ucfirst($schedule->priority) }}
                                </span>
                            </div>

                            {{-- Assign Technician --}}
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            // Transform schedule data ke FullCalendar events
            const events = @json($formattedEvents);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,listWeek'
                },
                events: events,
                eventDisplay: 'block',
                height: 'auto',
                locale: 'id',
                eventDidMount: function(info) {
                    info.el.style.borderRadius = '6px';
                    info.el.style.padding = '2px 6px';
                    info.el.style.fontSize = '0.75rem';
                    info.el.style.fontWeight = '600';
                    info.el.style.cursor = 'pointer';
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    alert(
                        `Mesin: ${info.event.title}\n` +
                        `Tanggal: ${info.event.startStr}\n` +
                        `Status: ${props.status}\n` +
                        `Prioritas: ${props.priority}\n` +
                        `Teknisi: ${props.technician}`
                    );
                }
            });

            calendar.render();
        });
    </script>
@endpush
