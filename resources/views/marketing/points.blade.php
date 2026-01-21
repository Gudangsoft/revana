@extends('marketing.layouts.app')

@section('title', 'Point Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-trophy"></i> Point Saya
    </h4>
</div>

<!-- Point Summary -->
<div class="card mb-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
    <div class="card-body text-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5>Total Point Anda</h5>
                <div class="display-3 fw-bold">{{ $marketing->total_points }}</div>
                <p class="mb-0 opacity-75">Point didapatkan dari setiap artikel yang berhasil disubmit</p>
            </div>
            <div class="col-md-4 text-end">
                <i class="bi bi-star-fill" style="font-size: 5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Point Info -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle"></i>
    <strong>Sistem Point Marketing:</strong> Setiap artikel yang berhasil disubmit akan memberikan <strong>+10 point</strong>.
</div>

<!-- Point History -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Riwayat Perolehan Point
    </div>
    <div class="card-body">
        @if($pointHistories->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Point</th>
                        <th>Keterangan</th>
                        <th>Artikel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pointHistories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <span class="badge bg-success fs-6">+{{ $history->points_earned }}</span>
                        </td>
                        <td>{{ $history->description }}</td>
                        <td>
                            @if($history->submission)
                            <code>{{ $history->submission->kode_submit }}</code>
                            <br>
                            <small class="text-muted">{{ Str::limit($history->submission->judul_artikel, 30) }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $pointHistories->links() }}
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="bi bi-star" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum Ada Point</h5>
            <p>Point akan bertambah ketika artikel Anda berhasil disubmit.</p>
        </div>
        @endif
    </div>
</div>
@endsection
