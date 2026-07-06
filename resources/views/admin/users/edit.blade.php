@extends('layouts.app')
@section('title', 'Edit Teknisi')
@section('page-title', 'Edit Teknisi')

@section('content')
    <div class="card-custom">
        <div class="card-header-custom">
            <h6>Edit Teknisi</h6>
        </div>
        <div class="card-body-custom">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru <span class="text-muted">(kosongkan jika tidak ingin
                            mengubah)</span></label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <button type="submit" class="btn btn-gradient-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>
@endsection
