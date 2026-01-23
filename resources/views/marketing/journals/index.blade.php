@extends('marketing.layouts.app')

@section('title', 'Data Jurnal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-journal-text"></i> Data Jurnal</h4>
    <span class="badge bg-primary fs-6">Total: {{ $journals->total() }} jurnal</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Jurnal</th>
                            <th>Kode Jurnal</th>
                            <th>Publisher</th>
                            <th>Akreditasi</th>
                            <th>Point</th>
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
                                @if($journal->link_jurnal)
                                    <br><small class="text-muted">
                                        <a href="{{ $journal->link_jurnal }}" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-link-45deg"></i> Link Jurnal
                                        </a>
                                    </small>
                                @endif
                            </td>
                            <td>
                                <code class="badge bg-light text-dark">{{ $journal->kode_jurnal }}</code>
                            </td>
                            <td>{{ $journal->publisher ?? '-' }}</td>
                            <td>
                                @if($journal->accreditation)
                                    <span class="badge bg-success">{{ $journal->accreditation }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">{{ $journal->points }} Point</span>
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
        </div>
        <div class="card-footer bg-white">
            {{ $journals->links() }}
        </div>
    </div>
@endsection
