@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Detail Pendaftaran Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-badge"></i> Informasi Pendaftar
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status:</strong></div>
                    <div class="col-md-8">
                        @if($registration->status === 'pending')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        @elseif($registration->status === 'approved')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Approved
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Rejected
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Nama Lengkap:</strong></div>
                    <div class="col-md-8">{{ $registration->full_name }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Email:</strong></div>
                    <div class="col-md-8">{{ $registration->email }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Afiliasi:</strong></div>
                    <div class="col-md-8">{{ $registration->affiliation }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>No WhatsApp:</strong></div>
                    <div class="col-md-8">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $registration->whatsapp) }}" 
                           target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-whatsapp"></i> {{ $registration->whatsapp }}
                        </a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Bidang Ilmu:</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-info">{{ $registration->field_of_study }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Bahasa Artikel:</strong></div>
                    <div class="col-md-8">
                        @if($registration->article_languages && is_array($registration->article_languages))
                            @foreach($registration->article_languages as $lang)
                                <span class="badge bg-secondary me-1">
                                    @if($lang === 'Indonesia')
                                        <i class="bi bi-flag-fill text-danger"></i>
                                    @else
                                        <i class="bi bi-flag-fill text-primary"></i>
                                    @endif
                                    {{ $lang }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>ID SINTA:</strong></div>
                    <div class="col-md-8">{{ $registration->sinta_id }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>ID Scopus:</strong></div>
                    <div class="col-md-8">{{ $registration->scopus_id ?: '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Tanggal Daftar:</strong></div>
                    <div class="col-md-8">{{ $registration->created_at->format('d M Y H:i') }}</div>
                </div>

                @if($registration->notes)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Catatan:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-info mb-0">{{ $registration->notes }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-gear"></i> Aksi
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.reviewer-registrations.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                    @if($registration->status === 'pending')
                        <form action="{{ route('admin.reviewer-registrations.approve', $registration) }}" 
                              method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Setujui pendaftaran ini dan buat akun reviewer?')">
                                <i class="bi bi-check-circle"></i> Setujui & Buat Akun
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Tolak Pendaftaran
                        </button>
                    @endif

                    <form action="{{ route('admin.reviewer-registrations.destroy', $registration) }}" 
                          method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" 
                                onclick="return confirm('Hapus data pendaftaran ini?')">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.reviewer-registrations.reject', $registration) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tolak pendaftaran dari <strong>{{ $registration->full_name }}</strong>?</p>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" name="notes" rows="3" required 
                                  placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
