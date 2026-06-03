@extends('layouts.app')

@section('title', 'Pengaturan Point')
@section('page-title', 'Pengaturan Point')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-coins me-2"></i>Pengaturan Point PIC & Marketing
                    </h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.task-point-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- PIC Point Settings -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="m-0"><i class="fas fa-user-tie me-2"></i>Point PIC (Per Tugas)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Task Key</th>
                                                <th>Label Tugas</th>
                                                <th width="15%">Point</th>
                                                <th width="10%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($picSettings as $index => $setting)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><code>{{ $setting->task_key }}</code></td>
                                                    <td>{{ $setting->task_label }}</td>
                                                    <td>
                                                        <input type="number"
                                                               name="points[{{ $setting->id }}]"
                                                               value="{{ $setting->points }}"
                                                               class="form-control form-control-sm text-center"
                                                               min="0" step="0.01">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-flex justify-content-center">
                                                            <input type="checkbox" 
                                                                   class="form-check-input" 
                                                                   name="is_active[{{ $setting->id }}]" 
                                                                   value="1"
                                                                   {{ $setting->is_active ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        Belum ada pengaturan point PIC
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Marketing Point Settings -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="m-0"><i class="fas fa-bullhorn me-2"></i>Point Marketing (Per Submission)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Task Key</th>
                                                <th>Label Tugas</th>
                                                <th width="15%">Point</th>
                                                <th width="10%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($marketingSettings as $index => $setting)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><code>{{ $setting->task_key }}</code></td>
                                                    <td>{{ $setting->task_label }}</td>
                                                    <td>
                                                        <input type="number"
                                                               name="points[{{ $setting->id }}]"
                                                               value="{{ $setting->points }}"
                                                               class="form-control form-control-sm text-center"
                                                               min="0" step="0.01">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-flex justify-content-center">
                                                            <input type="checkbox" 
                                                                   class="form-check-input" 
                                                                   name="is_active[{{ $setting->id }}]" 
                                                                   value="1"
                                                                   {{ $setting->is_active ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        Belum ada pengaturan point Marketing
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="alert alert-warning mb-0 py-2 px-3" style="font-size:.82rem; max-width:680px;">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Nilai desimal didukung</strong> (contoh: <code>0.25</code>, <code>0.33</code>, <code>1.5</code>).
                                &nbsp;|&nbsp;
                                <strong>Perubahan nilai hanya berlaku untuk transaksi baru ke depan.</strong>
                                Data historis yang sudah tercatat <em>tidak berubah otomatis</em>.
                                Gunakan tombol <strong>Sync Ulang Poin</strong> di halaman Laporan Point
                                jika ingin recalculate semua data dengan nilai baru.
                            </div>
                            <button type="submit" class="btn btn-primary flex-shrink-0">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Add New Task Point -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="m-0"><i class="fas fa-plus-circle me-2"></i>Tambah Task Point Baru</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.task-point-settings.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Tipe User</label>
                                            <select name="user_type" class="form-select" required>
                                                <option value="">Pilih Tipe...</option>
                                                <option value="pic">PIC</option>
                                                <option value="marketing">Marketing</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Task Key</label>
                                            <input type="text" name="task_key" class="form-control" 
                                                   placeholder="contoh: review1" required>
                                            <small class="text-muted">Kode unik tanpa spasi</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Label Tugas</label>
                                            <input type="text" name="task_label" class="form-control" 
                                                   placeholder="contoh: Review 1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Point</label>
                                            <input type="number" name="points" class="form-control"
                                                   value="1" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-plus me-2"></i>Tambah Task
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
