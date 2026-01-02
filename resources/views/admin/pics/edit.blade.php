@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Edit PIC')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Form Edit PIC
            </div>
            <div class="card-body">
                <form action="{{ route('admin.pics.update', $pic) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $pic->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role">
                            <option value="">Pilih Role</option>
                            <option value="AUTOR 1" {{ old('role', $pic->role) == 'AUTOR 1' ? 'selected' : '' }}>AUTOR 1</option>
                            <option value="EDITOR 1" {{ old('role', $pic->role) == 'EDITOR 1' ? 'selected' : '' }}>EDITOR 1</option>
                            <option value="REVIEWER 1" {{ old('role', $pic->role) == 'REVIEWER 1' ? 'selected' : '' }}>REVIEWER 1</option>
                            <option value="REVIEWER 2" {{ old('role', $pic->role) == 'REVIEWER 2' ? 'selected' : '' }}>REVIEWER 2</option>
                            <option value="AUTOR 2" {{ old('role', $pic->role) == 'AUTOR 2' ? 'selected' : '' }}>AUTOR 2</option>
                            <option value="COPY EDITING + PRODUCTION + PUBLISH" {{ old('role', $pic->role) == 'COPY EDITING + PRODUCTION + PUBLISH' ? 'selected' : '' }}>COPY EDITING + PRODUCTION + PUBLISH</option>
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email', $pic->email) }}" 
                               placeholder="email@example.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" value="{{ old('phone', $pic->phone) }}" 
                               placeholder="081234567890">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   id="is_active" {{ old('is_active', $pic->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Status Aktif
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('admin.pics.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
