@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Formulir Review Artikel Ilmiah')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.certificates.index') }}" class="nav-link">
        <i class="bi bi-award-fill"></i> Sertifikat
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> FORMULIR REVIEW ARTIKEL ILMIAH</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reviewer.results.store', $assignment) }}" method="POST">
                    @csrf

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Jurnal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('journal_name') is-invalid @enderror" 
                                       name="journal_name" value="{{ old('journal_name') }}" required>
                                @error('journal_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Artikel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('article_code') is-invalid @enderror" 
                                       name="article_code" value="{{ old('article_code', $assignment->article_number ?? '') }}" required>
                                @error('article_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Judul Artikel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('article_title') is-invalid @enderror" 
                                       name="article_title" value="{{ old('article_title', $assignment->article_title ?? '') }}" required>
                                @error('article_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Reviewer</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                <small class="text-muted">Auto terisi dari profil Anda</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Review <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" 
                                       name="review_date" value="{{ old('review_date', date('Y-m-d')) }}" required>
                                @error('review_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Section I: Penilaian Substansi Artikel -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-list-check"></i> I. PENILAIAN SUBSTANSI ARTIKEL</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="35%">Aspek Penilaian</th>
                                    <th width="15%">Skor (1-5)</th>
                                    <th width="45%">Komentar Reviewer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $aspects = [
                                    1 => 'Kebaruan dan relevansi topik penelitian',
                                    2 => 'Kesesuaian judul dengan isi artikel',
                                    3 => 'Kejelasan latar belakang dan rumusan masalah',
                                    4 => 'Kejelasan tujuan dan kontribusi penelitian',
                                    5 => 'Ketepatan metode dan pendekatan penelitian',
                                    6 => 'Kualitas analisis dan pembahasan',
                                    7 => 'Kualitas hasil penelitian',
                                    8 => 'Kejelasan simpulan dan implikasi penelitian'
                                ];
                                @endphp

                                @foreach($aspects as $num => $aspect)
                                <tr>
                                    <td class="text-center">{{ $num }}</td>
                                    <td>{{ $aspect }}</td>
                                    <td>
                                        <select class="form-select @error('score_'.$num) is-invalid @enderror" 
                                                name="score_{{ $num }}" required>
                                            <option value="">Pilih</option>
                                            @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ old('score_'.$num) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error('score_'.$num)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <textarea class="form-control @error('comment_'.$num) is-invalid @enderror" 
                                                  name="comment_{{ $num }}" rows="2" required 
                                                  placeholder="Tulis komentar...">{{ old('comment_'.$num) }}</textarea>
                                        @error('comment_'.$num)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- Section II: Penilaian Teknis Penulisan -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-file-text"></i> II. PENILAIAN TEKNIS PENULISAN</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="80%">Kriteria Teknis</th>
                                    <th width="15%" class="text-center">Ya / Tidak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>Artikel mengikuti format dan sistematika jurnal</td>
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="technical_1" 
                                                   id="technical_1" value="1" {{ old('technical_1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="technical_1">Ya</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">2</td>
                                    <td>Bahasa dan tata tulis sesuai kaidah ilmiah</td>
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="technical_2" 
                                                   id="technical_2" value="1" {{ old('technical_2') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="technical_2">Ya</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">3</td>
                                    <td>Referensi memadai dan terkini</td>
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="technical_3" 
                                                   id="technical_3" value="1" {{ old('technical_3') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="technical_3">Ya</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- Section III: Saran Perbaikan -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-lightbulb"></i> III. SARAN PERBAIKAN UNTUK PENULIS</h5>
                    <div class="mb-4">
                        <textarea class="form-control @error('improvement_suggestions') is-invalid @enderror" 
                                  name="improvement_suggestions" rows="6" required 
                                  placeholder="Tuliskan saran perbaikan untuk penulis (minimal 3 poin)...">{{ old('improvement_suggestions') }}</textarea>
                        @error('improvement_suggestions')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Berikan saran perbaikan yang spesifik dan konstruktif untuk penulis</small>
                    </div>

                    <hr class="my-4">

                    <!-- Section IV: Rekomendasi -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-check-circle"></i> IV. REKOMENDASI REVIEWER</h5>
                    <div class="mb-4">
                        @php
                        $recommendations = [
                            'ACCEPT' => 'Diterima tanpa revisi',
                            'MINOR_REVISION' => 'Diterima dengan revisi minor',
                            'MAJOR_REVISION' => 'Diterima dengan revisi mayor',
                            'REJECT' => 'Ditolak'
                        ];
                        @endphp

                        @foreach($recommendations as $value => $label)
                        <div class="form-check mb-2">
                            <input class="form-check-input @error('recommendation') is-invalid @enderror" 
                                   type="radio" name="recommendation" id="rec_{{ $value }}" 
                                   value="{{ $value }}" {{ old('recommendation') == $value ? 'checked' : '' }} required>
                            <label class="form-check-label" for="rec_{{ $value }}">
                                <strong>{{ $label }}</strong>
                            </label>
                        </div>
                        @endforeach
                        @error('recommendation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Section V: Pernyataan Reviewer -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-person-check"></i> V. PERNYATAAN REVIEWER</h5>
                    <div class="alert alert-info">
                        <p class="mb-2">Saya menyatakan bahwa penilaian ini dilakukan secara objektif berdasarkan keilmuan, tanpa konflik kepentingan, dan sesuai dengan etika akademik.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nama Lengkap:</strong> {{ auth()->user()->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal:</strong> <span id="current-date">{{ date('d F Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Submit Button -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('reviewer.tasks.show', $assignment) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-send"></i> Submit Formulir Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table td, .table th {
    vertical-align: middle;
}
</style>
@endsection
</div>
@endsection
