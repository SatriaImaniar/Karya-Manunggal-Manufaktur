@extends('layouts.app')
@section('title', 'Data Historis Kerusakan')
@section('page-title', 'Data Historis & Kalkulasi TBM')

@section('content')
    <div class="row g-4">
        {{-- Form Input Data Historis --}}
        <div class="col-lg-5 animate-in">
            <div class="card-custom">
                <div class="card-header-custom">
                    <h6><i class="bi bi-plus-circle me-2"></i>Input Data Historis Baru</h6>
                </div>
                <div class="card-body-custom">
                    <form action="{{ route('admin.maintenance.history.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="machine_id" class="form-label fw-semibold">Pilih Mesin <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('machine_id') is-invalid @enderror" id="machine_id"
                                name="machine_id" required>
                                <option value="">-- Pilih Mesin --</option>
                                @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                        {{ $machine->code }} — {{ $machine->name }} ({{ $machine->operating_hours_per_day }}
                                        jam/hari)
                                    </option>
                                @endforeach
                            </select>
                            @error('machine_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="waktu_operasi_jam" class="form-label fw-semibold">
                                    Total Waktu Operasi (T) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01"
                                        class="form-control @error('waktu_operasi_jam') is-invalid @enderror"
                                        id="waktu_operasi_jam" name="waktu_operasi_jam"
                                        value="{{ old('waktu_operasi_jam') }}" placeholder="Contoh: 2400" required>
                                    <span class="input-group-text">jam</span>
                                    @error('waktu_operasi_jam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="text-muted">Total jam mesin beroperasi selama periode pengamatan</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jumlah_kerusakan" class="form-label fw-semibold">
                                    Jumlah Kerusakan (N) <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="1"
                                    class="form-control @error('jumlah_kerusakan') is-invalid @enderror"
                                    id="jumlah_kerusakan" name="jumlah_kerusakan" value="{{ old('jumlah_kerusakan') }}"
                                    placeholder="Contoh: 4" required>
                                @error('jumlah_kerusakan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Berapa kali mesin rusak</small>
                            </div>

                            <div class="col-md-6">
                                <label for="waktu_perbaikan_jam" class="form-label fw-semibold">
                                    Total Waktu Perbaikan (Tr) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0"
                                        class="form-control @error('waktu_perbaikan_jam') is-invalid @enderror"
                                        id="waktu_perbaikan_jam" name="waktu_perbaikan_jam"
                                        value="{{ old('waktu_perbaikan_jam') }}" placeholder="Contoh: 48" required>
                                    <span class="input-group-text">jam</span>
                                    @error('waktu_perbaikan_jam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <small class="text-muted">Total jam perbaikan</small>
                            </div>
                        </div>

                        <hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="period_start" class="form-label fw-semibold">Awal Periode <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('period_start') is-invalid @enderror"
                                    id="period_start" name="period_start" value="{{ old('period_start') }}" required>
                                @error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="period_end" class="form-label fw-semibold">Akhir Periode <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('period_end') is-invalid @enderror"
                                    id="period_end" name="period_end" value="{{ old('period_end') }}" required>
                                @error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="notes" class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                rows="2" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Rumus Preview --}}
                        <div class="mt-3 p-3 rounded-3" style="background:#f0f9ff;border:1px solid #bae6fd">
                            <h6 class="fw-bold text-primary mb-2" style="font-size:.85rem">
                                <i class="bi bi-calculator me-1"></i> Rumus yang akan dihitung:
                            </h6>
                            <div style="font-size:.8rem;color:#334155">
                                <strong>MTBF</strong> = T / N <span class="text-muted">(rata-rata waktu antar
                                    kerusakan)</span><br>
                                <strong>MTTR</strong> = Tr / N <span class="text-muted">(rata-rata waktu
                                    perbaikan)</span><br>
                                <strong>Interval PM</strong> = 0.7 × MTBF <span class="text-muted">(interval Preventif
                                    Maintenance)</span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-gradient-primary w-100">
                                <i class="bi bi-calculator me-2"></i>Hitung & Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Daftar Data Historis --}}
        <div class="col-lg-7 animate-in">
            <div class="card-custom">
                <div class="card-header-custom">
                    <h6><i class="bi bi-clock-history me-2"></i>Riwayat Data Historis</h6>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Mesin</th>
                                    <th>Periode</th>
                                    <th class="text-end">T (jam)</th>
                                    <th class="text-end">N</th>
                                    <th class="text-end">Tr (jam)</th>
                                    <th class="text-end">MTBF</th>
                                    <th class="text-end">MTTR</th>
                                    <th class="text-end">Interval Pm</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $history->machine->code }}</div>
                                            <div class="text-muted" style="font-size:.7rem">{{ $history->machine->name }}</div>
                                        </td>
                                        <td style="font-size:.8rem">
                                            {{ $history->period_start->format('d/m/Y') }}<br>
                                            <span class="text-muted">s.d. {{ $history->period_end->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($history->waktu_operasi_jam, 1) }}</td>
                                        <td class="text-end">{{ $history->jumlah_kerusakan }}</td>
                                        <td class="text-end">{{ number_format($history->waktu_perbaikan_jam, 1) }}</td>
                                        <td class="text-end fw-bold text-primary">{{ $history->mtbf }}</td>
                                        <td class="text-end fw-bold text-warning">{{ $history->mttr }}</td>
                                        <td class="text-end fw-bold text-success">{{ $history->tpm_interval }}</td>
                                        <td>
                                            <a href="{{ route('admin.maintenance.calculation', $history->machine) }}"
                                                class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox" style="font-size:2rem"></i>
                                            <p class="mt-2 mb-0">Belum ada data historis. Silakan input data baru.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $histories->links() }}
            </div>
        </div>
    </div>
@endsection