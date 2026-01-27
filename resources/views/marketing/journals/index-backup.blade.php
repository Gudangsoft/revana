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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Belum ada data jurnal</td>
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
