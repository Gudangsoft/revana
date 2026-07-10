@extends('marketing.layouts.app')

@section('title', 'Detail Artikel')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('marketing.submissions') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Artikel
            </a>
        </div>

        <!-- Submission Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> {{ $submission->kode_submit }}
                    </h5>
                    <span class="badge bg-light text-dark">
                        {{ $submission->status }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Kode Submit:</th>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                            </tr>
                            <tr>
                                <th>ID Artikel:</th>
                                <td>{{ $submission->id_artikel ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jurnal:</th>
                                <td>{{ $submission->journalSlot->journalMaster->nama_jurnal ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Volume/Nomor:</th>
                                <td>Vol. {{ $submission->journalSlot->volume }}, No. {{ $submission->journalSlot->nomor }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Submit:</th>
                                <td>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Penulis:</th>
                                <td>{{ $submission->nama_penulis }}</td>
                            </tr>
                            <tr>
                                <th>No HP:</th>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Link Submit:</th>
                                <td>
                                    @if($submission->link_artikel)
                                        <a href="{{ $submission->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-link-45deg"></i> Buka Link
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Username Author:</th>
                                <td><code>{{ $submission->username_author ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Password Author:</th>
                                <td><code>{{ $submission->password_author ?? '-' }}</code></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="text-muted">Judul Artikel:</h6>
                    <p class="lead">{{ $submission->judul_artikel }}</p>
                </div>
            </div>
        </div>

        {{-- LOA Card --}}
        @if($submission->kode_loa)
        <div class="card mb-4 border-success">
            <div class="card-header bg-success bg-opacity-10 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-check-fill text-success"></i>
                <span class="fw-semibold text-success">Letter of Acceptance (LOA) Tersedia</span>
                <code class="ms-1 small text-muted">{{ $submission->kode_loa }}</code>
            </div>
            <div class="card-body py-3">
                <div class="d-flex align-items-end gap-3 flex-wrap">
                    <div>
                        <label class="form-label fw-semibold mb-1 small">Tanggal Surat LOA</label>
                        <input type="date" id="loaTanggalInput" class="form-control form-control-sm"
                               value="{{ now()->toDateString() }}"
                               style="width:180px;">
                        <div class="form-text">Kosong = tanggal default jurnal / hari ini</div>
                    </div>
                    <div class="pb-4">
                        <a id="loaBtn"
                           href="{{ route('marketing.submissions.loa', $submission) }}"
                           target="_blank" class="btn btn-success btn-sm px-4">
                            <i class="bi bi-eye me-1"></i> Lihat LOA
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var inp = document.getElementById('loaTanggalInput');
            var btn = document.getElementById('loaBtn');
            var base = btn.href.split('?')[0];
            function updateUrl() {
                btn.href = inp.value ? base + '?tanggal=' + inp.value : base;
            }
            inp.addEventListener('change', updateUrl);
            updateUrl();
        })();
        </script>
        @endif

        {{-- Kwitansi Card --}}
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary bg-opacity-10 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-receipt text-primary"></i>
                <span class="fw-semibold text-primary">Kwitansi Pembayaran</span>
            </div>
            <div class="card-body py-3">
                <a href="{{ route('marketing.submissions.kwitansi', $submission) }}"
                   target="_blank" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-eye me-1"></i> Lihat / Cetak Kwitansi
                </a>
                <div class="form-text mt-1">Nama pembayar, jumlah, dan keterangan bisa diisi/diubah langsung di halaman kwitansi — tidak disimpan ke database.</div>
            </div>
        </div>

        {{-- Invoice Card --}}
        <div class="card mb-4 border-info">
            <div class="card-header bg-info bg-opacity-10 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text text-info"></i>
                <span class="fw-semibold text-info">Invoice</span>
            </div>
            <div class="card-body py-3">
                <a href="{{ route('marketing.submissions.invoice', $submission) }}"
                   target="_blank" class="btn btn-info btn-sm px-4 text-white">
                    <i class="bi bi-eye me-1"></i> Lihat / Cetak Invoice
                </a>
                <div class="form-text mt-1">Info rekening otomatis dari Master Invoice, CP Marketing otomatis dari akun Anda yang sedang login — keduanya tetap bisa diubah manual di halaman invoice.</div>
            </div>
        </div>

        <!-- Progress Card - Using Shared Component -->
        <x-tracking-table :submission="$submission" />

        <!-- Catatan Marketing -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Catatan Marketing</h5>
            </div>
            <div class="card-body">
                @if(session('catatan_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('catatan_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($submission->catatan_marketing_at)
                <div class="alert alert-light border d-flex align-items-center gap-2 py-2 mb-3">
                    <i class="bi bi-clock-history text-warning fs-5"></i>
                    <div>
                        <div class="small fw-semibold">Terakhir disimpan:</div>
                        <div class="small text-muted">{{ $submission->catatan_marketing_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @endif

                <form action="{{ route('marketing.submissions.update-catatan', $submission) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="catatan_marketing" class="form-label">Catatan dari Marketing <small class="text-muted">(jika ada revisi setelah di submit/proses)</small></label>
                        <textarea name="catatan_marketing" id="catatan_marketing" class="form-control" rows="4"
                                  placeholder="Tulis catatan di sini...">{{ old('catatan_marketing', $submission->catatan_marketing) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Simpan Catatan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
