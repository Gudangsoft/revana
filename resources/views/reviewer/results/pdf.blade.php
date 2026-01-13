<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Review Artikel Ilmiah SIPERA</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 30px;
        }
        h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        h1.subtitle {
            font-size: 11pt;
            font-weight: normal;
            margin-top: 0;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.info-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10pt;
        }
        table.info-table td:first-child {
            width: 180px;
            font-weight: bold;
        }
        table.assessment-table {
            border: 1px solid #000;
            font-size: 10pt;
        }
        table.assessment-table th,
        table.assessment-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        table.assessment-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .section {
            margin-bottom: 20px;
        }
        .checkbox {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 11px;
            font-size: 10pt;
            font-weight: bold;
        }
        .checkbox.checked::before {
            content: "V";
        }
        .signature-box {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
        }
        .signature-image {
            max-width: 150px;
            max-height: 60px;
            margin-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        p {
            margin: 5px 0;
        }
        .ethics-section {
            margin-left: 20px;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <h1>Formulir Review Artikel Ilmiah SIPERA</h1>
    <h1 class="subtitle">(Untuk Reviewer/Mitra Bestari – Bahasa Indonesia)</h1>

    <!-- A. INFORMASI NASKAH -->
    <div class="section">
        <h2>A. Informasi Naskah</h2>
        <table class="info-table">
            <tr>
                <td>ID Manuskrip</td>
                <td>: {{ $result->manuscript_id }}</td>
            </tr>
            <tr>
                <td>Judul Manuskrip</td>
                <td>: {{ $result->manuscript_title }}</td>
            </tr>
            <tr>
                <td>Jenis Artikel</td>
                <td>: 
                    <span class="checkbox {{ $result->article_type == 'Research Article' ? 'checked' : '' }}"></span> Research Article
                    <span class="checkbox {{ $result->article_type == 'Review' ? 'checked' : '' }}"></span> Review
                </td>
            </tr>
            <tr>
                <td>Bidang/Section/Topik</td>
                <td>: {{ $result->field_section_topic }}</td>
            </tr>
            <tr>
                <td>Tanggal Review</td>
                <td>: {{ \Carbon\Carbon::parse($result->review_date)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- B. PERNYATAAN KONFLIK KEPENTINGAN & ETIKA -->
    <div class="section">
        <h2>B. Pernyataan Konflik Kepentingan & Etika</h2>
        <div class="ethics-section">
            <p><strong>1. Apakah Anda memiliki konflik kepentingan terhadap manuskrip ini?</strong></p>
            <p>
                <span class="checkbox {{ !$result->conflict_of_interest ? 'checked' : '' }}"></span> Tidak &nbsp;&nbsp;
                <span class="checkbox {{ $result->conflict_of_interest ? 'checked' : '' }}"></span> Ya
                @if($result->conflict_of_interest && $result->conflict_explanation)
                    (jelaskan): {{ $result->conflict_explanation }}
                @endif
            </p>

            <p><strong>2. Apakah Anda mendeteksi plagiarisme/kemiripan tinggi?</strong></p>
            <p>
                <span class="checkbox {{ !$result->plagiarism_detected ? 'checked' : '' }}"></span> Tidak &nbsp;&nbsp;
                <span class="checkbox {{ $result->plagiarism_detected ? 'checked' : '' }}"></span> Ya
                @if($result->plagiarism_detected && $result->plagiarism_explanation)
                    <br>(jelaskan bagian/indikasi): {{ $result->plagiarism_explanation }}
                @endif
            </p>

            <p><strong>3. Apakah Anda mendeteksi self-citation yang tidak relevan/berlebihan?</strong></p>
            <p>
                <span class="checkbox {{ !$result->excessive_self_citation ? 'checked' : '' }}"></span> Tidak &nbsp;&nbsp;
                <span class="checkbox {{ $result->excessive_self_citation ? 'checked' : '' }}"></span> Ya
                @if($result->excessive_self_citation && $result->self_citation_explanation)
                    <br>(jelaskan): {{ $result->self_citation_explanation }}
                @endif
            </p>

            <p><strong>4. Apakah ada masalah etik lain (misalnya data, consent, manipulasi sitasi, dsb.)?</strong></p>
            <p>
                <span class="checkbox {{ !$result->other_ethical_issues ? 'checked' : '' }}"></span> Tidak &nbsp;&nbsp;
                <span class="checkbox {{ $result->other_ethical_issues ? 'checked' : '' }}"></span> Ya
                @if($result->other_ethical_issues && $result->ethical_issues_explanation)
                    <br>(jelaskan): {{ $result->ethical_issues_explanation }}
                @endif
            </p>

            <p><strong>5. Pernyataan penggunaan AI oleh Reviewer</strong></p>
            <p>
                <span class="checkbox {{ !$result->ai_usage_statement ? 'checked' : '' }}"></span> Saya menegaskan bahwa saya tidak menggunakan AI generatif/AI-assisted untuk menulis laporan review ini.
            </p>
            <p>
                <span class="checkbox {{ $result->ai_usage_statement ? 'checked' : '' }}"></span> Saya menggunakan AI-assisted untuk membantu bahasa/penyusunan
            </p>
        </div>
    </div>

    <!-- C. PENILAIAN CEPAT (Rating Umum) -->
    <div class="section">
        <h2>C. Penilaian Cepat (Rating Umum)</h2>
        <p style="font-size: 10pt; margin-bottom: 10px;">Beri nilai 1–5 (1 = sangat buruk, 5 = sangat baik)</p>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="45%">Aspek</th>
                    <th width="12%">Skor (1–5)</th>
                    <th width="38%">Catatan Singkat</th>
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
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $rating['label'] }}</td>
                    <td style="text-align: center;">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="checkbox {{ $result->{'rating_'.$rating['field']} == $i ? 'checked' : '' }}"></span>{{ $i }} 
                        @endfor
                    </td>
                    <td>{{ $result->{'rating_'.$rating['field'].'_note'} ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- D. CHECKLIST EVALUASI DETAIL -->
    <div class="section">
        <h2>D. Checklist Evaluasi Detail (Ya/Tidak/Perlu Perbaikan)</h2>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="60%">Pertanyaan</th>
                    <th width="10%">Ya</th>
                    <th width="12%">Tidak</th>
                    <th width="13%">Perlu Perbaikan</th>
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
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $checklist['label'] }}</td>
                    <td style="text-align: center;">
                        <span class="checkbox {{ $result->{'checklist_'.$checklist['field']} == 'Ya' ? 'checked' : '' }}"></span>
                    </td>
                    <td style="text-align: center;">
                        <span class="checkbox {{ $result->{'checklist_'.$checklist['field']} == 'Tidak' ? 'checked' : '' }}"></span>
                    </td>
                    <td style="text-align: center;">
                        <span class="checkbox {{ $result->{'checklist_'.$checklist['field']} == 'Perlu Perbaikan' ? 'checked' : '' }}"></span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- E. EVALUASI REFERENSI -->
    <div class="section">
        <h2>E. Evaluasi Referensi</h2>
        <div class="ethics-section">
            <p><strong>1. Referensi sudah relevan & mencukupi?</strong></p>
            <p>
                <span class="checkbox {{ $result->references_adequate ? 'checked' : '' }}"></span> Ya &nbsp;&nbsp;
                <span class="checkbox {{ !$result->references_adequate ? 'checked' : '' }}"></span> Tidak
            </p>

            <p><strong>2. Saran referensi tambahan (Wajib tulis lengkap + DOI bila ada):</strong></p>
            @if($result->suggested_references)
            <p style="margin-left: 20px; text-align: justify; white-space: pre-wrap;">{{ $result->suggested_references }}</p>
            @else
            <p style="margin-left: 20px;">-</p>
            @endif
        </div>
    </div>

    <!-- F. REKOMENDASI AKHIR REVIEWER -->
    <div class="section">
        <h2>F. Rekomendasi Akhir Reviewer</h2>
        <div class="ethics-section">
            <p><strong>Pilih satu:</strong></p>
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
            <p>
                <span class="checkbox {{ $result->recommendation == $value ? 'checked' : '' }}"></span> {{ $label }}
            </p>
            @endforeach

            <p><strong>Alasan singkat rekomendasi:</strong></p>
            <p style="margin-left: 20px; text-align: justify; white-space: pre-wrap;">{{ $result->recommendation_reason }}</p>
        </div>
    </div>

    <!-- PERNYATAAN REVIEWER -->
    <div class="section">
        <div class="signature-box">
            <p style="text-align: justify; margin-bottom: 15px;">
                <strong>Pernyataan Reviewer:</strong> Saya menyatakan bahwa penilaian ini dilakukan secara objektif 
                berdasarkan keilmuan, tanpa konflik kepentingan, dan sesuai dengan etika akademik.
            </p>
            <table class="info-table" style="margin-top: 10px;">
                <tr>
                    <td width="150px">Nama Lengkap</td>
                    <td>: {{ $result->reviewer_signature ?? $result->reviewer_name }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ \Carbon\Carbon::parse($result->statement_date ?? $result->review_date)->format('d F Y') }}</td>
                </tr>
            </table>
            @if($reviewer && $reviewer->signature)
            <div style="margin-top: 15px;">
                <strong>Tanda Tangan:</strong><br>
                <img src="{{ public_path('storage/' . $reviewer->signature) }}" 
                     alt="Signature" 
                     class="signature-image">
            </div>
            @endif
        </div>
    </div>
</body>
</html>
