@extends('layouts.app')

@section('title', 'Edit Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Edit Submission Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pencil"></i> Edit Submission Fasttrack</span>
                <span class="badge bg-dark">{{ $submission->kode_submit }}</span>
            </div>
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('admin.fasttrack.update', $submission->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jurnal</label>
                                <input type="text" class="form-control" 
                                       value="{{ $submission->journalSlot->journalMaster->nama_jurnal ?? '-' }}" 
                                       disabled readonly>
                                <small class="text-muted">Jurnal tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Slot</label>
                                <input type="text" class="form-control" 
                                       value="Vol. {{ $submission->journalSlot->volume ?? '-' }} No. {{ $submission->journalSlot->issue ?? '-' }} ({{ $submission->journalSlot->year ?? '-' }})" 
                                       disabled readonly>
                                <small class="text-muted">Slot tidak dapat diubah</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="judul_artikel" class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('judul_artikel') is-invalid @enderror" 
                                          id="judul_artikel" 
                                          name="judul_artikel" 
                                          rows="2" 
                                          required>{{ old('judul_artikel', $submission->judul_artikel) }}</textarea>
                                @error('judul_artikel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="link_publish" class="form-label">Link Publish <span class="text-danger">*</span></label>
                                <input type="url" 
                                       class="form-control @error('link_publish') is-invalid @enderror" 
                                       id="link_publish" 
                                       name="link_publish" 
                                       placeholder="https://..." 
                                       value="{{ old('link_publish', $submission->link_publish) }}"
                                       required>
                                <small class="text-muted"><i class="bi bi-link-45deg"></i> Link artikel yang sudah publish (Opsional: Jika belum ada, artikel perlu penugasan)</small>
                                @error('link_publish')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penulis" class="form-label">Nama Penulis <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nama_penulis') is-invalid @enderror" 
                                       id="nama_penulis" 
                                       name="nama_penulis" 
                                       value="{{ old('nama_penulis', $submission->nama_penulis) }}"
                                       required>
                                @error('nama_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_hp_penulis" class="form-label">No HP Penulis</label>
                                <input type="text" 
                                       class="form-control @error('no_hp_penulis') is-invalid @enderror" 
                                       id="no_hp_penulis" 
                                       name="no_hp_penulis" 
                                       value="{{ old('no_hp_penulis', $submission->no_hp_penulis) }}">
                                @error('no_hp_penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-chat-left-text"></i> Catatan</h6>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2">{{ old('notes', $submission->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><i class="bi bi-people"></i> PIC & Petugas</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="marketing_id" class="form-label">PIC Marketing</label>
                                <select class="form-select @error('marketing_id') is-invalid @enderror" id="marketing_id" name="marketing_id">
                                    <option value="">-- Pilih PIC Marketing --</option>
                                    @foreach($marketings as $marketing)
                                        <option value="{{ $marketing->id }}" {{ old('marketing_id', $submission->marketing_id) == $marketing->id ? 'selected' : '' }}>
                                            {{ $marketing->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('marketing_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="petugas_submit_id" class="form-label">PIC Submit</label>
                                <select class="form-select @error('petugas_submit_id') is-invalid @enderror" id="petugas_submit_id" name="petugas_submit_id">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($pics as $pic)
                                        <option value="{{ $pic->id }}" {{ old('petugas_submit_id', $submission->petugas_submit_id) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('petugas_submit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.fasttrack.show', $submission->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
