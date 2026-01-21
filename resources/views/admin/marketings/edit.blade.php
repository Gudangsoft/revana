@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Edit Marketing')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Form Edit Marketing
            </div>
            <div class="card-body">
                <form action="{{ route('admin.marketings.update', $marketing) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $marketing->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email', $marketing->email) }}" 
                               placeholder="email@example.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            @if($marketing->password)
                            <i class="bi bi-check-circle text-success"></i> Password sudah diatur.
                            @else
                            <i class="bi bi-exclamation-circle text-warning"></i> Password belum diatur.
                            @endif
                            Kosongkan jika tidak ingin mengubah.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone', $marketing->phone) }}" 
                               placeholder="081234567890">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', $marketing->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Status Aktif
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('admin.marketings.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
