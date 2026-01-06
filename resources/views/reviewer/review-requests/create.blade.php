@extends('layouts.app')

@section('title', ' - Ajukan Permintaan Review')
@section('page-title', 'Ajukan Permintaan Review')

@section('sidebar')
    @include('reviewer.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-plus"></i> Ajukan Permintaan Review</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Informasi:</strong> 
                    Anda dapat mengajukan diri untuk mereview jurnal. Tentukan jumlah jurnal yang ingin Anda review dan estimasi waktu penyelesaian (maksimal 5 hari per jurnal).
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reviewer.review-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="number_of_journals" class="form-label">Jumlah Jurnal <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('number_of_journals') is-invalid @enderror" 
                               id="number_of_journals" 
                               name="number_of_journals" 
                               value="{{ old('number_of_journals') }}"
                               min="1" 
                               style="max-width: 200px;"
                               required>
                        <small class="form-text text-muted">Masukkan jumlah jurnal yang ingin Anda review</small>
                        @error('number_of_journals')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="number_of_days" class="form-label">Lama Hari (Per Jurnal) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('number_of_days') is-invalid @enderror" 
                               id="number_of_days" 
                               name="number_of_days" 
                               value="{{ old('number_of_days') }}"
                               min="1" 
                               max="5"
                               style="max-width: 200px;" 
                               required>
                        <small class="form-text text-muted">Estimasi waktu untuk menyelesaikan review per jurnal (maksimal 5 hari)</small>
                        @error('number_of_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="Tambahkan catatan atau informasi tambahan mengenai permintaan Anda...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('reviewer.review-requests.my-requests') }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
