<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Review Artikel Ilmiah</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 40px;
        }
        h1 {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info-table td {
            padding: 5px;
            vertical-align: top;
        }
        table.info-table td:first-child {
            width: 150px;
            font-weight: bold;
        }
        table.assessment-table {
            border: 1px solid #000;
        }
        table.assessment-table th,
        table.assessment-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        table.assessment-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .section {
            margin-bottom: 25px;
        }
        .recommendation {
            display: inline-block;
            padding: 5px 10px;
            border: 2px solid #000;
            margin: 5px 0;
        }
        .signature-section {
            margin-top: 40px;
            text-align: left;
        }
        .signature-box {
            margin-top: 10px;
            border: 1px solid #000;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .signature-image {
            max-width: 200px;
            max-height: 80px;
            margin-top: 10px;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #000;
            margin-right: 5px;
            text-align: center;
            vertical-align: middle;
            line-height: 13px;
            font-size: 12pt;
            font-weight: bold;
        }
        .checkbox.checked {
            background-color: #000;
        }
        .checkbox.checked::before {
            content: "✓";
            color: #fff;
        }
        .total-score {
            font-size: 14pt;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Formulir Review Artikel Ilmiah</h1>

    <!-- Basic Information -->
    <table class="info-table">
        <tr>
            <td>Nama Jurnal</td>
            <td>: {{ $result->journal_name }}</td>
        </tr>
        <tr>
            <td>Kode Artikel</td>
            <td>: {{ $result->article_code }}</td>
        </tr>
        <tr>
            <td>Judul Artikel</td>
            <td>: {{ $result->article_title }}</td>
        </tr>
        <tr>
            <td>Nama Reviewer</td>
            <td>: {{ $result->reviewer_name }}</td>
        </tr>
        <tr>
            <td>Tanggal Review</td>
            <td>: {{ \Carbon\Carbon::parse($result->review_date)->format('d F Y') }}</td>
        </tr>
    </table>

    <!-- Section I: Penilaian Substansi -->
    <div class="section">
        <h2>I. PENILAIAN SUBSTANSI ARTIKEL</h2>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Aspek Penilaian</th>
                    <th width="10%">Skor</th>
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
                $totalScore = 0;
                @endphp

                @foreach($aspects as $num => $aspect)
                @php
                    $score = $result->{'score_'.$num};
                    $totalScore += $score;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $num }}</td>
                    <td>{{ $aspect }}</td>
                    <td style="text-align: center;">{{ $score }}</td>
                    <td>{{ $result->{'comment_'.$num} }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total-score">Total Skor: {{ $totalScore }}/40</div>
    </div>

    <!-- Section II: Penilaian Teknis -->
    <div class="section">
        <h2>II. PENILAIAN TEKNIS PENULISAN</h2>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="80%">Kriteria Teknis</th>
                    <th width="15%" style="text-align: center;">Ya / Tidak</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>Artikel mengikuti format dan sistematika jurnal</td>
                    <td style="text-align: center;">
                        @if($result->technical_1)
                            <strong>[V]</strong>
                        @else
                            <strong>[ ]</strong>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">2</td>
                    <td>Bahasa dan tata tulis sesuai kaidah ilmiah</td>
                    <td style="text-align: center;">
                        @if($result->technical_2)
                            <strong>[V]</strong>
                        @else
                            <strong>[ ]</strong>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">3</td>
                    <td>Referensi memadai dan terkini</td>
                    <td style="text-align: center;">
                        @if($result->technical_3)
                            <strong>[V]</strong>
                        @else
                            <strong>[ ]</strong>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Section III: Saran Perbaikan -->
    <div class="section">
        <h2>III. SARAN PERBAIKAN UNTUK PENULIS</h2>
        <p style="text-align: justify; white-space: pre-wrap;">{{ $result->improvement_suggestions }}</p>
    </div>

    <!-- Section IV: Rekomendasi -->
    <div class="section">
        <h2>IV. REKOMENDASI REVIEWER</h2>
        @php
        $recommendations = [
            'ACCEPT' => 'Diterima tanpa revisi',
            'MINOR_REVISION' => 'Diterima dengan revisi minor',
            'MAJOR_REVISION' => 'Diterima dengan revisi mayor',
            'REJECT' => 'Ditolak'
        ];
        @endphp

        @foreach($recommendations as $value => $label)
        <div style="margin-bottom: 5px;">
            @if($result->recommendation == $value)
                <strong>[V] {{ $label }}</strong>
            @else
                <strong>[ ] {{ $label }}</strong>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Section V: Pernyataan Reviewer -->
    <div class="section">
        <h2>V. PERNYATAAN REVIEWER</h2>
        <div class="signature-box">
            <p style="text-align: justify;">
                Saya menyatakan bahwa penilaian ini dilakukan secara objektif berdasarkan keilmuan, 
                tanpa konflik kepentingan, dan sesuai dengan etika akademik.
            </p>
            <table class="info-table" style="margin-top: 20px;">
                <tr>
                    <td width="150px">Nama Lengkap</td>
                    <td>: {{ $result->reviewer_signature ?? $result->reviewer_name }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ \Carbon\Carbon::parse($result->statement_date ?? $result->review_date)->format('d F Y') }}</td>
                </tr>
            </table>
            @if($reviewer->signature)
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
