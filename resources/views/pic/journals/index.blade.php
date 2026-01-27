@extends('pic.layouts.app')

@section('title', 'Data Jurnal')
@section('page-title', 'Data Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
                <div>
                    <a href="{{ route('pic.journal-slots.monitoring') }}" class="btn btn-info">
                        <i class="bi bi-bar-chart"></i> Pemantauan Slot
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Search & Filter Form -->
                <form action="{{ route('pic.journals.index') }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="🔍 Cari nama jurnal..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="akreditasi">
                                <option value="">-- Akreditasi --</option>
                                <option value="Sinta 1" {{ request('akreditasi') == 'Sinta 1' ? 'selected' : '' }}>Sinta 1</option>
                                <option value="Sinta 2" {{ request('akreditasi') == 'Sinta 2' ? 'selected' : '' }}>Sinta 2</option>
                                <option value="Sinta 3" {{ request('akreditasi') == 'Sinta 3' ? 'selected' : '' }}>Sinta 3</option>
                                <option value="Sinta 4" {{ request('akreditasi') == 'Sinta 4' ? 'selected' : '' }}>Sinta 4</option>
                                <option value="Sinta 5" {{ request('akreditasi') == 'Sinta 5' ? 'selected' : '' }}>Sinta 5</option>
                                <option value="Sinta 6" {{ request('akreditasi') == 'Sinta 6' ? 'selected' : '' }}>Sinta 6</option>
                                <option value="Non Sinta" {{ request('akreditasi') == 'Non Sinta' ? 'selected' : '' }}>Non Sinta</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="kategori">
                                <option value="">-- Kategori --</option>
                                <option value="Penelitian" {{ request('kategori') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                                <option value="PKM" {{ request('kategori') == 'PKM' ? 'selected' : '' }}>PKM</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="jenis">
                                <option value="">-- Jenis --</option>
                                <option value="Jurnal Nasional" {{ request('jenis') == 'Jurnal Nasional' ? 'selected' : '' }}>Nasional</option>
                                <option value="Jurnal Internasional" {{ request('jenis') == 'Jurnal Internasional' ? 'selected' : '' }}>Internasional</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('pic.journals.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                        @if(request()->hasAny(['search', 'akreditasi', 'kategori', 'jenis']))
                        <div class="col-auto">
                            <span class="badge bg-info py-2 px-3">
                                <i class="bi bi-funnel"></i> {{ collect(request()->only(['search', 'akreditasi', 'kategori', 'jenis']))->filter()->count() }} filter aktif
                            </span>
                        </div>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Volume</th>
                                <th>Slot</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th class="hide-mobile">Terbitan</th>
                                <th class="hide-mobile">Marketing</th>
                                <th class="hide-mobile">PIC</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $journal)
                            <tr>
                                <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                                <td>
                                    <strong>{{ Str::limit($journal->title, 60) }}</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="{{ $journal->link }}" target="_blank">Lihat Jurnal</a>
                                    </small>
                                </td>
                                <td>
                                    @if($journal->volume)
                                        <span class="badge bg-secondary">{{ $journal->volume }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong class="text-primary">{{ $journal->slot ?? 0 }}</strong>
                                </td>
                                <td><span class="badge bg-info">{{ $journal->accreditation }}</span></td>
                                <td><span class="badge bg-success">{{ $journal->points }} pts</span></td>
                                <td class="hide-mobile">
                                    @if($journal->publisher)
                                        <small>{{ Str::limit($journal->publisher, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="hide-mobile">
                                    @if($journal->marketing)
                                        <small>{{ Str::limit($journal->marketing, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="hide-mobile">
                                    @if($journal->pic)
                                        <small>{{ Str::limit($journal->pic, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>{{ $journal->creator->name }}</td>
                                <td>{{ $journal->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ $journal->link }}" target="_blank" class="btn btn-sm btn-info" title="Lihat Jurnal">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">Belum ada data jurnal</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $journals])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
                            @endif
                        </td>
                        <td>
                            @if($journal->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data jurnal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $journals->links() }}
        </div>
    </div>
</div>
@endsection
