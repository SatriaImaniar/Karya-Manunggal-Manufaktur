@extends('layouts.app')
@section('title', 'Tambah Teknisi')
@section('page-title', 'Tambah Teknisi')

@section('content')
    <div class="card-custom">
        <div class="card-header-custom">
            <h6>Tambah Teknisi Baru</h6>
        </div>
        <div class="card-body-custom">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-gradient-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>
@endsection
