@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tambah/Kurangi Point')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-coin"></i> Form Tambah/Kurangi Point
            </div>
            <div class="card-body">
                <form action="{{ route('admin.points.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                name="user_id" id="reviewerSelect" required>
                            <option value="">-- Pilih Reviewer --</option>
                            @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" 
                                    data-name="{{ $reviewer->name }}"
                                    data-total="{{ $reviewer->total_points }}"
                                    data-available="{{ $reviewer->available_points }}"
                                    {{ old('user_id') == $reviewer->id ? 'selected' : '' }}>
                                {{ $reviewer->name }} - {{ $reviewer->email }}
                                @if($reviewer->article_languages)
                                    [{{ implode(', ', $reviewer->article_languages) }}]
                                @endif
                                (Available: {{ $reviewer->available_points }} pts)
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="reviewerInfo" class="alert alert-info" style="display: none;">
                        <strong>Reviewer:</strong> <span id="infoName"></span><br>
                        <strong>Total Points:</strong> <span id="infoTotal"></span><br>
                        <strong>Available Points:</strong> <span id="infoAvailable"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                name="type" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="EARNED" {{ old('type') == 'EARNED' ? 'selected' : '' }}>
                                Tambah Point (EARNED)
                            </option>
                            <option value="REDEEMED" {{ old('type') == 'REDEEMED' ? 'selected' : '' }}>
                                Kurangi Point (REDEEMED)
                            </option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Point <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('points') is-invalid @enderror" 
                               name="points" 
                               value="{{ old('points') }}"
                               min="1" 
                               required>
                        @error('points')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="4" required
                                  placeholder="Jelaskan alasan penambahan/pengurangan point...">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Simpan
                        </button>
                        <a href="{{ route('admin.points.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Panduan:</h6>
                <ul class="small">
                    <li><strong>EARNED:</strong> Menambah point ke reviewer (reward manual, bonus, dll)</li>
                    <li><strong>REDEEMED:</strong> Mengurangi point dari reviewer (penalti, koreksi, dll)</li>
                    <li>Point yang dikurangi tidak boleh lebih besar dari available points</li>
                    <li>Transaksi yang dibuat manual bisa dihapus</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-exclamation-triangle"></i> Perhatian
            </div>
            <div class="card-body">
                <p class="small mb-0">
                    Pastikan alasan penambahan/pengurangan point jelas dan terdokumentasi dengan baik.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reviewerSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('reviewerInfo');
    
    if (this.value) {
        document.getElementById('infoName').textContent = option.dataset.name;
        document.getElementById('infoTotal').textContent = option.dataset.total + ' pts';
        document.getElementById('infoAvailable').textContent = option.dataset.available + ' pts';
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>
@endsection

