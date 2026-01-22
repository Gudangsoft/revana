@extends('marketing.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-journal-text"></i> Data Jurnal</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Jurnal</th>
                            <th>ISSN</th>
                            <th>E-ISSN</th>
                            <th>Penerbit</th>
                            <th>Akreditasi</th>
                            <th>Total Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $index => $journal)
                        <tr>
                            <td>{{ $journals->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $journal->nama_jurnal }}</strong>
                                @if($journal->website)
                                    <br><small class="text-muted">
                                        <a href="{{ $journal->website }}" target="_blank">
                                            <i class="bi bi-link-45deg"></i> {{ $journal->website }}
                                        </a>
                                    </small>
                                @endif
                            </td>
                            <td>{{ $journal->issn ?? '-' }}</td>
                            <td>{{ $journal->e_issn ?? '-' }}</td>
                            <td>{{ $journal->penerbit ?? '-' }}</td>
                            <td>
                                @if($journal->akreditasi)
                                    <span class="badge bg-success">{{ $journal->akreditasi }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $journal->journalSlots->count() }} Slot</span>
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
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2 mb-0">Tidak ada data jurnal</p>
                            </td>
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
</div>
@endsection
