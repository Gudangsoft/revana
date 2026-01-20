@extends('pic.layouts.app')

@section('title', 'Data Akreditasi')
@section('page-title', 'Data Akreditasi')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-award"></i> Daftar Tingkat Akreditasi
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Poin</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accreditations as $accreditation)
                    <tr>
                        <td>{{ $loop->iteration + ($accreditations->currentPage() - 1) * $accreditations->perPage() }}</td>
                        <td>
                            @php
                                $peringkatColors = [
                                    'SINTA 1' => 'primary',
                                    'SINTA 2' => 'success',
                                    'SINTA 3' => 'info',
                                    'SINTA 4' => 'warning',
                                    'SINTA 5' => 'secondary',
                                    'SINTA 6' => 'dark',
                                ];
                            @endphp
                            <span class="badge bg-{{ $peringkatColors[$accreditation->name] ?? 'secondary' }}">
                                {{ $accreditation->name }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-primary">{{ $accreditation->points }}</span>
                        </td>
                        <td>{{ $accreditation->description ?? '-' }}</td>
                        <td>
                            @if($accreditation->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non-Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data akreditasi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $accreditations->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Jurnal per Akreditasi -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-journal-bookmark"></i> Jurnal per Tingkat Akreditasi
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($accreditations as $accreditation)
                @if($accreditation->journals && $accreditation->journals->count() > 0)
                <div class="col-md-6 mb-3">
                    <div class="card border-{{ $peringkatColors[$accreditation->name] ?? 'secondary' }}">
                        <div class="card-header bg-{{ $peringkatColors[$accreditation->name] ?? 'secondary' }} text-white py-2">
                            {{ $accreditation->name }} ({{ $accreditation->journals->count() }} jurnal)
                        </div>
                        <div class="card-body py-2">
                            <ul class="list-unstyled mb-0 small">
                                @foreach($accreditation->journals->take(5) as $journal)
                                    <li><i class="bi bi-journal"></i> {{ $journal->nama_jurnal }}</li>
                                @endforeach
                                @if($accreditation->journals->count() > 5)
                                    <li class="text-muted">... dan {{ $accreditation->journals->count() - 5 }} jurnal lainnya</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
