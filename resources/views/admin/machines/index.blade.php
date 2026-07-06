@extends('layouts.app')
@section('title', 'Data Mesin')
@section('page-title', 'Data Master Mesin')

@section('content')
<div class="card-custom animate-in">
    <div class="card-header-custom">
        <h6><i class="bi bi-cpu me-2"></i>Daftar Mesin ({{ $machines->total() }})</h6>
        <a href="{{ route('admin.machines.create') }}" class="btn btn-sm btn-gradient-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Mesin
        </a>
    </div>
    <div class="card-body-custom p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mesin</th>
                        <th>Tipe</th>
                        <th>Lokasi</th>
                        <th>Operasi/Hari</th>
                        <th>Status</th>
                        <th>Histori</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($machines as $machine)
                        <tr>
                            <td><span class="fw-bold text-primary">{{ $machine->code }}</span></td>
                            <td>{{ $machine->name }}</td>
                            <td>{{ $machine->type ?? '-' }}</td>
                            <td>{{ $machine->location ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info">{{ $machine->operating_hours_per_day }} jam</span>
                            </td>
                            <td>
                                <span class="badge badge-status bg-{{ $machine->status_badge }}">
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.maintenance.calculation', $machine) }}"
                                   class="text-decoration-none">
                                    {{ $machine->maintenance_histories_count }} record
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.machines.edit', $machine) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.machines.destroy', $machine) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus mesin ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size:2rem"></i>
                                <p class="mt-2 mb-0">Belum ada data mesin.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $machines->links() }}
</div>
@endsection
