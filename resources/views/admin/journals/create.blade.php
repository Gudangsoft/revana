@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Tambah Jurnal Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Form Tambah Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journals.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Judul Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title') }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Jurnal <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('link') is-invalid @enderror" 
                               name="link" value="{{ old('link') }}" 
                               placeholder="https://..." required>
                        @error('link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akreditasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" 
                                name="accreditation" required>
                            <option value="">Pilih Akreditasi</option>
                            @foreach($accreditations as $accreditation)
                                <option value="{{ $accreditation->name }}" {{ old('accreditation') == $accreditation->name ? 'selected' : '' }}>
                                    {{ $accreditation->name }} ({{ $accreditation->points }} points)
                                </option>
                            @endforeach
                        </select>
                        @error('accreditation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Points akan diberikan otomatis berdasarkan akreditasi</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Terbitan</label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" 
                               name="publisher" value="{{ old('publisher') }}" 
                               placeholder="Nama penerbit jurnal">
                        @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marketing 
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMarketingModal">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </label>
                        <select class="form-select @error('marketing') is-invalid @enderror" 
                                name="marketing" id="marketing_select">
                            <option value="">Pilih Marketing</option>
                            @foreach($marketings as $marketing)
                                <option value="{{ $marketing->name }}" {{ old('marketing') == $marketing->name ? 'selected' : '' }}>
                                    {{ $marketing->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('marketing')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PIC (Person In Charge) 
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPicModal">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </label>
                        <select class="form-select @error('pic') is-invalid @enderror" 
                                name="pic" id="pic_select">
                            <option value="">Pilih PIC</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->name }}" {{ old('pic') == $pic->name ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pic')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">User Author</label>
                        <input type="text" class="form-control @error('author_username') is-invalid @enderror" 
                               name="author_username" value="{{ old('author_username') }}" 
                               placeholder="Username author jurnal">
                        @error('author_username')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Author</label>
                        <input type="text" class="form-control @error('author_password') is-invalid @enderror" 
                               name="author_password" value="{{ old('author_password') }}" 
                               placeholder="Password author jurnal">
                        @error('author_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Turnitin</label>
                        <input type="url" class="form-control @error('turnitin_link') is-invalid @enderror" 
                               name="turnitin_link" value="{{ old('turnitin_link') }}" 
                               placeholder="https://...">
                        @error('turnitin_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary">
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
                <h6>Sistem Point:</h6>
                <ul class="list-unstyled">
                    <li>🥇 SINTA 1 = 100 points</li>
                    <li>🥈 SINTA 2 = 80 points</li>
                    <li>🥉 SINTA 3 = 60 points</li>
                    <li>📊 SINTA 4 = 40 points</li>
                    <li>📈 SINTA 5 = 20 points</li>
                    <li>📉 SINTA 6 = 10 points</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Point akan otomatis diberikan ke reviewer setelah hasil review disetujui.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Marketing -->
<div class="modal fade" id="addMarketingModal" tabindex="-1" aria-labelledby="addMarketingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMarketingModalLabel">
                    <i class="bi bi-megaphone"></i> Tambah Marketing Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMarketingForm" action="{{ route('admin.marketings.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Marketing <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="marketing_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="marketing_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="phone" id="marketing_phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="marketing_is_active" value="1" checked>
                        <label class="form-check-label" for="marketing_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add PIC -->
<div class="modal fade" id="addPicModal" tabindex="-1" aria-labelledby="addPicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPicModalLabel">
                    <i class="bi bi-person-badge"></i> Tambah PIC Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPicForm" action="{{ route('admin.pics.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama PIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="pic_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="pic_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="phone" id="pic_phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="pic_is_active" value="1" checked>
                        <label class="form-check-label" for="pic_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill marketing name after adding via modal
document.getElementById('addMarketingForm').addEventListener('submit', function(e) {
    const marketingName = document.getElementById('marketing_name').value;
    if (marketingName) {
        const selectElement = document.getElementById('marketing_select');
        // Add new option
        const newOption = new Option(marketingName, marketingName, true, true);
        selectElement.add(newOption);
    }
});

// Auto-fill PIC name after adding via modal
document.getElementById('addPicForm').addEventListener('submit', function(e) {
    const picName = document.getElementById('pic_name').value;
    if (picName) {
        const selectElement = document.getElementById('pic_select');
        // Add new option
        const newOption = new Option(picName, picName, true, true);
        selectElement.add(newOption);
    }
});
</script>
@endsection

