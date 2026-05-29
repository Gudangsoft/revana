@extends('layouts.app')

@section('title', 'Referensi Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Referensi Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bookmark-star-fill"></i> Data Referensi Jurnal</span>
        <a href="{{ route('admin.referensi-jurnals.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Filter --}}
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nama jurnal, jenis, bidang ilmu, referensi..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="number" name="tahun" class="form-control form-control-sm"
                       placeholder="Tahun" value="{{ request('tahun') }}" min="1900" max="2100">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if(request('search') || request('tahun'))
                <a href="{{ route('admin.referensi-jurnals.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i> Reset
                </a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Jurnal</th>
                        <th>Jenis Jurnal</th>
                        <th>Bidang Ilmu</th>
                        <th>Tahun</th>
                        <th>Referensi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referensiJurnals as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($referensiJurnals->currentPage() - 1) * $referensiJurnals->perPage() }}</td>
                        <td><strong>{{ $item->nama_jurnal }}</strong></td>
                        <td>{{ $item->jenis_jurnal }}</td>
                        <td>{{ $item->bidang_ilmu }}</td>
                        <td>{{ $item->tahun }}</td>
                        <td style="max-width:300px;">
                            <span class="d-inline-block text-truncate" style="max-width:280px;" title="{{ $item->referensi }}">
                                {{ $item->referensi }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.referensi-jurnals.edit', $item) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.referensi-jurnals.destroy', $item) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data referensi jurnal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.per-page-selector', ['paginator' => $referensiJurnals])
    </div>
</div>
@endsection
