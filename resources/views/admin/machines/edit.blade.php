@extends('layouts.app')
@section('title', 'Edit Mesin')
@section('page-title', 'Edit Mesin: ' . $machine->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom animate-in">
            <div class="card-header-custom">
                <h6><i class="bi bi-pencil-square me-2"></i>Edit Mesin — {{ $machine->code }}</h6>
                <a href="{{ route('admin.machines.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body-custom">
                <form action="{{ route('admin.machines.update', $machine) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="code" class="form-label fw-semibold">Kode Mesin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code', $machine->code) }}" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label for="name" class="form-label fw-semibold">Nama Mesin <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $machine->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">Tipe / Model</label>
                            <input type="text" class="form-control @error('type') is-invalid @enderror"
                                   id="type" name="type" value="{{ old('type', $machine->type) }}">
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Lokasi</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location" value="{{ old('location', $machine->location) }}">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="operating_hours_per_day" class="form-label fw-semibold">
                                Jam Operasi / Hari <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" max="24"
                                       class="form-control @error('operating_hours_per_day') is-invalid @enderror"
                                       id="operating_hours_per_day" name="operating_hours_per_day"
                                       value="{{ old('operating_hours_per_day', $machine->operating_hours_per_day) }}" required>
                                <span class="input-group-text">jam</span>
                                @error('operating_hours_per_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <small class="text-muted">Rata-rata mesin menyala per hari (0.5 - 24 jam)</small>
                        </div>
                        <div class="col-md-4">
                            <label for="installation_date" class="form-label fw-semibold">Tanggal Instalasi</label>
                            <input type="date" class="form-control @error('installation_date') is-invalid @enderror"
                                   id="installation_date" name="installation_date"
                                   value="{{ old('installation_date', $machine->installation_date?->format('Y-m-d')) }}">
                            @error('installation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $machine->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $machine->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="maintenance" {{ old('status', $machine->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.machines.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-gradient-primary">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
