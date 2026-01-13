@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Formulir Review Artikel Ilmiah SIPERA')

@section('sidebar')
    @include('reviewer.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> FORMULIR REVIEW ARTIKEL ILMIAH SIPERA</h5>
                <small>(Untuk Reviewer/Mitra Bestari – Bahasa Indonesia)</small>
            </div>
            <div class="card-body">
                <form action="{{ route('reviewer.results.store', $assignment) }}" method="POST">
                    @csrf

                    <!-- A. INFORMASI NASKAH -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-file-text"></i> A. Informasi Naskah</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID Manuskrip <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('manuscript_id') is-invalid @enderror" 
                                       name="manuscript_id" value="{{ old('manuscript_id', $assignment->article_number ?? '') }}" required>
                                @error('manuscript_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Judul Manuskrip <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('manuscript_title') is-invalid @enderror" 
                                       name="manuscript_title" value="{{ old('manuscript_title', $assignment->article_title ?? '') }}" required>
                                @error('manuscript_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis Artikel <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="article_type" id="type_research" 
                                               value="Research Article" {{ old('article_type') == 'Research Article' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="type_research">Research Article</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="article_type" id="type_review" 
                                               value="Review" {{ old('article_type') == 'Review' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="type_review">Review</label>
                                    </div>
                                </div>
                                @error('article_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bidang/Section/Topik <span class="text-danger">*</span></label>
                                @php
                                    $hasFieldOfStudy = !empty($assignment->field_of_study_id) && $assignment->fieldOfStudy;
                                @endphp
                                
                                @if($hasFieldOfStudy)
                                    <input type="text" class="form-control bg-light" 
                                           value="{{ $assignment->fieldOfStudy->name }}" 
                                           readonly>
                                    <input type="hidden" name="field_section_topic" value="{{ $assignment->fieldOfStudy->name }}">
                                    <small class="text-success"><i class="bi bi-check-circle"></i> Bidang ditentukan saat penugasan</small>
                                @else
                                    <input type="text" class="form-control @error('field_section_topic') is-invalid @enderror" 
                                           name="field_section_topic" 
                                           value="{{ old('field_section_topic') }}" 
                                           placeholder="Contoh: Teknik Informatika, Kedokteran, Ekonomi, dll" 
                                           required>
                                    <small class="text-muted"><i class="bi bi-pencil"></i> Silakan isi bidang artikel secara manual</small>
                                    @error('field_section_topic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- B. PERNYATAAN KONFLIK KEPENTINGAN & ETIKA -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-shield-check"></i> B. Pernyataan Konflik Kepentingan & Etika</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">1. Apakah Anda memiliki konflik kepentingan terhadap manuskrip ini? <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="conflict_of_interest" id="conflict_no" 
                                       value="0" {{ old('conflict_of_interest') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="conflict_no">Tidak</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="conflict_of_interest" id="conflict_yes" 
                                       value="1" {{ old('conflict_of_interest') === '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="conflict_yes">Ya (jelaskan):</label>
                            </div>
                        </div>
                        <input type="text" class="form-control mt-2 @error('conflict_explanation') is-invalid @enderror" 
                               name="conflict_explanation" id="conflict_explanation" 
                               value="{{ old('conflict_explanation') }}" placeholder="Jelaskan jika ada konflik kepentingan">
                        @error('conflict_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">2. Apakah Anda mendeteksi plagiarisme/kemiripan tinggi? <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plagiarism_detected" id="plagiarism_no" 
                                       value="0" {{ old('plagiarism_detected') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="plagiarism_no">Tidak</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="plagiarism_detected" id="plagiarism_yes" 
                                       value="1" {{ old('plagiarism_detected') === '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="plagiarism_yes">Ya (jelaskan bagian/indikasi):</label>
                            </div>
                        </div>
                        <textarea class="form-control mt-2 @error('plagiarism_explanation') is-invalid @enderror" 
                                  name="plagiarism_explanation" id="plagiarism_explanation" rows="2"
                                  placeholder="Jelaskan bagian/indikasi plagiarisme">{{ old('plagiarism_explanation') }}</textarea>
                        @error('plagiarism_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">3. Apakah Anda mendeteksi self-citation yang tidak relevan/berlebihan? <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="excessive_self_citation" id="self_citation_no" 
                                       value="0" {{ old('excessive_self_citation') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="self_citation_no">Tidak</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="excessive_self_citation" id="self_citation_yes" 
                                       value="1" {{ old('excessive_self_citation') === '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="self_citation_yes">Ya (jelaskan):</label>
                            </div>
                        </div>
                        <textarea class="form-control mt-2 @error('self_citation_explanation') is-invalid @enderror" 
                                  name="self_citation_explanation" id="self_citation_explanation" rows="2"
                                  placeholder="Jelaskan self-citation yang tidak relevan">{{ old('self_citation_explanation') }}</textarea>
                        @error('self_citation_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">4. Apakah ada masalah etik lain (misalnya data, consent, manipulasi sitasi, dsb.)? <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="other_ethical_issues" id="ethical_no" 
                                       value="0" {{ old('other_ethical_issues') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="ethical_no">Tidak</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="other_ethical_issues" id="ethical_yes" 
                                       value="1" {{ old('other_ethical_issues') === '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="ethical_yes">Ya (jelaskan):</label>
                            </div>
                        </div>
                        <textarea class="form-control mt-2 @error('ethical_issues_explanation') is-invalid @enderror" 
                                  name="ethical_issues_explanation" id="ethical_issues_explanation" rows="2"
                                  placeholder="Jelaskan masalah etik lainnya">{{ old('ethical_issues_explanation') }}</textarea>
                        @error('ethical_issues_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">5. Pernyataan penggunaan AI oleh Reviewer <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ai_usage_statement" id="ai_no" 
                                   value="0" {{ old('ai_usage_statement') === '0' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="ai_no">
                                Saya menegaskan bahwa saya <strong>tidak menggunakan AI generatif/AI-assisted</strong> untuk menulis laporan review ini.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ai_usage_statement" id="ai_yes" 
                                   value="1" {{ old('ai_usage_statement') === '1' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="ai_yes">
                                Saya <strong>menggunakan AI-assisted</strong> untuk membantu bahasa/penyusunan
                            </label>
                        </div>
                        @error('ai_usage_statement')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- C. PENILAIAN CEPAT (Rating Umum) -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-star"></i> C. Penilaian Cepat (Rating Umum)</h5>
                    <p class="text-muted mb-3">Beri nilai 1–5 (1 = sangat buruk, 5 = sangat baik)</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="40%">Aspek</th>
                                    <th width="20%">Skor (1–5)</th>
                                    <th width="35%">Catatan Singkat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $ratings = [
                                    ['field' => 'scope', 'label' => 'Kesesuaian dengan scope jurnal'],
                                    ['field' => 'novelty', 'label' => 'Kebaruan/Originalitas'],
                                    ['field' => 'significance', 'label' => 'Signifikansi kontribusi'],
                                    ['field' => 'soundness', 'label' => 'Kebenaran teknis/Scientific soundness'],
                                    ['field' => 'methodology', 'label' => 'Desain riset & metodologi'],
                                    ['field' => 'analysis', 'label' => 'Kualitas analisis & hasil'],
                                    ['field' => 'presentation', 'label' => 'Kualitas presentasi (struktur, alur)'],
                                    ['field' => 'figures', 'label' => 'Kualitas gambar/tabel'],
                                    ['field' => 'references', 'label' => 'Kualitas referensi/bibliografi'],
                                    ['field' => 'language', 'label' => 'Kualitas bahasa (Inggris/Indonesia)'],
                                ];
                                @endphp

                                @foreach($ratings as $index => $rating)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $rating['label'] }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            @for($i = 1; $i <= 5; $i++)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" 
                                                       name="rating_{{ $rating['field'] }}" 
                                                       id="rating_{{ $rating['field'] }}_{{ $i }}" 
                                                       value="{{ $i }}" 
                                                       {{ old('rating_'.$rating['field']) == $i ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="rating_{{ $rating['field'] }}_{{ $i }}">{{ $i }}</label>
                                            </div>
                                            @endfor
                                        </div>
                                        @error('rating_'.$rating['field'])
                                        <div class="invalid-feedback d-block text-center">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm @error('rating_'.$rating['field'].'_note') is-invalid @enderror" 
                                               name="rating_{{ $rating['field'] }}_note" 
                                               value="{{ old('rating_'.$rating['field'].'_note') }}" 
                                               placeholder="Catatan singkat...">
                                        @error('rating_'.$rating['field'].'_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- D. CHECKLIST EVALUASI DETAIL -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-list-check"></i> D. Checklist Evaluasi Detail (Ya/Tidak/Perlu Perbaikan)</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="55%">Pertanyaan</th>
                                    <th width="13%" class="text-center">Ya</th>
                                    <th width="13%" class="text-center">Tidak</th>
                                    <th width="14%" class="text-center">Perlu Perbaikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $checklists = [
                                    ['field' => 'abstract', 'label' => 'Abstrak jelas & sesuai isi'],
                                    ['field' => 'intro', 'label' => 'Pendahuluan memberi latar belakang cukup'],
                                    ['field' => 'novelty', 'label' => 'Novelty dinyatakan jelas'],
                                    ['field' => 'literature', 'label' => 'Tinjauan pustaka relevan & mutakhir'],
                                    ['field' => 'method', 'label' => 'Metode dijelaskan rinci & dapat direplikasi'],
                                    ['field' => 'design', 'label' => 'Desain eksperimen/penelitian tepat'],
                                    ['field' => 'results', 'label' => 'Hasil disajikan jelas (grafik/tabel tepat)'],
                                    ['field' => 'discussion', 'label' => 'Diskusi membandingkan dengan studi sebelumnya'],
                                    ['field' => 'conclusion', 'label' => 'Kesimpulan didukung data/hasil'],
                                    ['field' => 'data_availability', 'label' => 'Data/kode tersedia atau dijelaskan aksesnya (jika perlu)'],
                                ];
                                @endphp

                                @foreach($checklists as $index => $checklist)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $checklist['label'] }}</td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="radio" 
                                               name="checklist_{{ $checklist['field'] }}" 
                                               id="checklist_{{ $checklist['field'] }}_yes" 
                                               value="Ya" 
                                               style="width: 20px; height: 20px; cursor: pointer;"
                                               {{ old('checklist_'.$checklist['field']) == 'Ya' ? 'checked' : '' }} required>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="radio" 
                                               name="checklist_{{ $checklist['field'] }}" 
                                               id="checklist_{{ $checklist['field'] }}_no" 
                                               value="Tidak" 
                                               style="width: 20px; height: 20px; cursor: pointer;"
                                               {{ old('checklist_'.$checklist['field']) == 'Tidak' ? 'checked' : '' }} required>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="radio" 
                                               name="checklist_{{ $checklist['field'] }}" 
                                               id="checklist_{{ $checklist['field'] }}_improvement" 
                                               value="Perlu Perbaikan" 
                                               style="width: 20px; height: 20px; cursor: pointer;"
                                               {{ old('checklist_'.$checklist['field']) == 'Perlu Perbaikan' ? 'checked' : '' }} required>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- E. EVALUASI REFERENSI -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-book"></i> E. Evaluasi Referensi</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">1. Referensi sudah relevan & mencukupi? <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="references_adequate" id="ref_adequate_yes" 
                                   value="1" {{ old('references_adequate') === '1' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="ref_adequate_yes">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="references_adequate" id="ref_adequate_no" 
                                   value="0" {{ old('references_adequate') === '0' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="ref_adequate_no">Tidak</label>
                        </div>
                        @error('references_adequate')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">2. Saran referensi tambahan <span class="text-muted">(Wajib tulis lengkap + DOI bila ada)</span></label>
                        <textarea class="form-control @error('suggested_references') is-invalid @enderror" 
                                  name="suggested_references" rows="6"
                                  placeholder="Tuliskan saran referensi tambahan dengan format lengkap:&#10;1) Nama Penulis (Tahun). Judul. Jurnal/Penerbit. DOI: ...&#10;2) ...&#10;3) ...">{{ old('suggested_references') }}</textarea>
                        @error('suggested_references')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 3 referensi yang relevan jika diperlukan</small>
                    </div>

                    <hr class="my-4">

                    <!-- F. REKOMENDASI AKHIR REVIEWER -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-check-circle"></i> F. Rekomendasi Akhir Reviewer</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih satu: <span class="text-danger">*</span></label>
                        @php
                        $recommendations = [
                            'ACCEPT' => 'Terima tanpa revisi (Accept in present form)',
                            'MINOR_REVISION' => 'Terima dengan revisi minor (Accept after minor revision)',
                            'MAJOR_REVISION' => 'Revisi mayor – tinjau ulang (Major revision / Reconsider after major revision)',
                            'REJECT_RESUBMIT' => 'Tolak – dapat submit ulang jika diperbaiki (Reject but resubmission possible)',
                            'REJECT' => 'Tolak – tidak disarankan submit ulang (Reject – serious flaws)'
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

                    <div class="mb-4">
                        <label class="form-label fw-bold">Alasan singkat rekomendasi: <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('recommendation_reason') is-invalid @enderror" 
                                  name="recommendation_reason" rows="5" required
                                  placeholder="Jelaskan alasan rekomendasi Anda...">{{ old('recommendation_reason') }}</textarea>
                        @error('recommendation_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Berikan penjelasan yang jelas dan konstruktif untuk mendukung rekomendasi Anda</small>
                    </div>

                    <hr class="my-4">

                    <!-- PERNYATAAN REVIEWER -->
                    <div class="alert alert-info">
                        <p class="mb-3"><strong>Pernyataan Reviewer:</strong> Saya menyatakan bahwa penilaian ini dilakukan secara objektif berdasarkan keilmuan, tanpa konflik kepentingan, dan sesuai dengan etika akademik.</p>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>Nama Lengkap:</strong> {{ auth()->user()->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal:</strong> <span id="current-date">{{ date('d F Y') }}</span>
                            </div>
                        </div>
                        @if(auth()->user()->signature)
                        <div class="mt-3">
                            <strong>Tanda Tangan:</strong><br>
                            <img src="{{ asset('storage/' . auth()->user()->signature) }}" 
                                 alt="Signature" 
                                 class="mt-2"
                                 style="max-width: 200px; max-height: 80px;">
                        </div>
                        @else
                        <div class="mt-3">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Perhatian:</strong> Anda belum mengupload tanda tangan digital. 
                                Silakan upload di <a href="{{ route('reviewer.profile.edit') }}" target="_blank">halaman profil</a>.
                            </div>
                        </div>
                        @endif
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

/* Radio button styling - make them more visible */
input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #6c757d;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
    background-color: white;
    margin: 0;
}

input[type="radio"]:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(13, 110, 253, 0.3);
}

input[type="radio"]:checked {
    border-color: #0d6efd;
    background-color: #0d6efd;
}

input[type="radio"]:checked::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: white;
}

.form-check-label {
    cursor: pointer;
    margin-left: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle visibility of explanation fields based on radio selection
    const radioGroups = {
        'conflict_of_interest': 'conflict_explanation',
        'plagiarism_detected': 'plagiarism_explanation',
        'excessive_self_citation': 'self_citation_explanation',
        'other_ethical_issues': 'ethical_issues_explanation'
    };

    Object.keys(radioGroups).forEach(radioName => {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        const explanationField = document.getElementById(radioGroups[radioName]);
        
        if (explanationField) {
            // Initial state - hide if "Tidak" is selected
            const selectedRadio = document.querySelector(`input[name="${radioName}"]:checked`);
            if (selectedRadio && selectedRadio.value === '0') {
                explanationField.style.display = 'none';
                explanationField.value = '';
                explanationField.removeAttribute('required');
            }
            
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === '1') {
                        // Show explanation field when "Ya" is selected
                        explanationField.style.display = 'block';
                        explanationField.focus();
                    } else {
                        // Hide explanation field when "Tidak" is selected
                        explanationField.style.display = 'none';
                        explanationField.value = '';
                        explanationField.removeAttribute('required');
                    }
                });
            });
        }
    });
});
</script>
@endsection
