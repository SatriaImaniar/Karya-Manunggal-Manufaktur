@extends('layouts.app')
@section('title', 'Jenis Kerusakan')
@section('page-title', 'Master Data Jenis Kerusakan')

@section('content')
<div class="row g-4">

    {{-- ============================================================
         FORM TAMBAH JENIS KERUSAKAN
         ============================================================ --}}
    <div class="col-lg-4 animate-in">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6><i class="bi bi-plus-circle me-2"></i>Tambah Jenis Kerusakan</h6>
            </div>
            <div class="card-body-custom">
                <form action="{{ route('admin.jenis-kerusakan.store') }}" method="POST">
                    @csrf

                    {{-- Nama Kerusakan --}}
                    <div class="mb-3">
                        <label for="nama_kerusakan" class="form-label fw-semibold">
                            Nama Kerusakan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nama_kerusakan') is-invalid @enderror"
                               id="nama_kerusakan" name="nama_kerusakan"
                               value="{{ old('nama_kerusakan') }}"
                               placeholder="Contoh: Trafo terbakar, Bearing rusak" required>
                        @error('nama_kerusakan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mesin Terkait (Opsional) --}}
                    <div class="mb-3">
                        <label for="machine_id" class="form-label fw-semibold">
                            Mesin Terkait
                            <span class="text-muted fw-normal" style="font-size:.8rem">(opsional)</span>
                        </label>
                        <select class="form-select @error('machine_id') is-invalid @enderror"
                                id="machine_id" name="machine_id">
                            <option value="">— Semua Mesin / Generik —</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}"
                                    {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->code }} — {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('machine_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Biarkan kosong jika berlaku untuk semua mesin.</small>
                    </div>

                    {{-- Estimasi Downtime --}}
                    <div class="mb-3">
                        <label for="estimasi_downtime_jam" class="form-label fw-semibold">
                            Estimasi Downtime
                            <span class="text-muted fw-normal" style="font-size:.8rem">(opsional)</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   class="form-control @error('estimasi_downtime_jam') is-invalid @enderror"
                                   id="estimasi_downtime_jam" name="estimasi_downtime_jam"
                                   value="{{ old('estimasi_downtime_jam') }}"
                                   placeholder="Contoh: 4.5">
                            <span class="input-group-text">jam</span>
                            @error('estimasi_downtime_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Estimasi lama downtime jika jenis kerusakan ini terjadi.</small>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                  id="deskripsi" name="deskripsi" rows="3"
                                  placeholder="Deskripsi detail jenis kerusakan (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-gradient-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Jenis Kerusakan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         TABEL DAFTAR JENIS KERUSAKAN
         ============================================================ --}}
    <div class="col-lg-8 animate-in">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6><i class="bi bi-list-ul me-2"></i>Daftar Jenis Kerusakan</h6>
                <span class="badge bg-secondary">{{ $jenisKerusakanList->total() }} data</span>
            </div>
            <div class="card-body-custom p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Nama Kerusakan</th>
                                <th>Mesin</th>
                                <th class="text-end">Est. Downtime</th>
                                <th>Deskripsi</th>
                                <th>Dipakai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jenisKerusakanList as $jk)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $jk->nama_kerusakan }}</div>
                                    </td>
                                    <td>
                                        @if($jk->machine)
                                            <div class="fw-semibold" style="font-size:.85rem">{{ $jk->machine->code }}</div>
                                            <div class="text-muted" style="font-size:.75rem">{{ $jk->machine->name }}</div>
                                        @else
                                            <span class="text-muted fst-italic" style="font-size:.82rem">Semua Mesin</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($jk->estimasi_downtime_jam !== null)
                                            <span class="fw-semibold text-warning">
                                                {{ number_format((float)$jk->estimasi_downtime_jam, 2) }}
                                            </span>
                                            <span class="text-muted" style="font-size:.75rem"> jam</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width:180px">
                                        <span style="font-size:.82rem;color:#64748b">
                                            {{ $jk->deskripsi ? Str::limit($jk->deskripsi, 50) : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $usedCount = $jk->maintenanceHistories->count() @endphp
                                        @if($usedCount > 0)
                                            <span class="badge bg-info text-white">{{ $usedCount }}x</span>
                                        @else
                                            <span class="text-muted" style="font-size:.8rem">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            {{-- Tombol Edit --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $jk->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.jenis-kerusakan.destroy', $jk) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Hapus jenis kerusakan \"{{ $jk->nama_kerusakan }}\"?\n\nData historis yang menggunakan jenis ini akan tetap ada (field jenis_kerusakan menjadi kosong).')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size:2.5rem"></i>
                                        <p class="mt-2 mb-0">Belum ada data jenis kerusakan.</p>
                                        <p class="mb-0" style="font-size:.85rem">Gunakan form di sebelah kiri untuk menambahkan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $jenisKerusakanList->links() }}
        </div>
    </div>
</div>
@endsection

{{-- ============================================================
     MODAL EDIT — diletakkan di luar section content
     ============================================================ --}}
@foreach($jenisKerusakanList as $jk)
<div class="modal fade" id="editModal{{ $jk->id }}" tabindex="-1"
     aria-labelledby="editModalLabel{{ $jk->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.jenis-kerusakan.update', $jk) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="editModalLabel{{ $jk->id }}">
                        <i class="bi bi-pencil-square me-2"></i>Edit Jenis Kerusakan
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    {{-- Nama Kerusakan --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Kerusakan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="nama_kerusakan"
                               value="{{ $jk->nama_kerusakan }}" required>
                    </div>

                    {{-- Mesin Terkait --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Mesin Terkait
                            <span class="text-muted fw-normal" style="font-size:.8rem">(opsional)</span>
                        </label>
                        <select class="form-select" name="machine_id">
                            <option value="">— Semua Mesin / Generik —</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}"
                                    {{ $jk->machine_id == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->code }} — {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Estimasi Downtime --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estimasi Downtime</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0"
                                   class="form-control" name="estimasi_downtime_jam"
                                   value="{{ $jk->estimasi_downtime_jam }}"
                                   placeholder="Contoh: 4.5">
                            <span class="input-group-text">jam</span>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3">{{ $jk->deskripsi }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
