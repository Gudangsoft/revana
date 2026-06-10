{{-- PIC & Marketing Point Rankings --}}
<div class="row mt-4">
    {{-- PIC Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point PIC</span>
                <a href="{{ route('admin.pics.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($topPics->isEmpty() || $topPics->sum('total_points') == 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity:.4;"></i>
                    <p class="mb-0 mt-2">Belum ada peringkat</p>
                    <small>Point akan muncul setelah PIC menyelesaikan tugas</small>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama PIC</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPics->filter(fn($p) => ($p->total_points ?? 0) > 0) as $index => $pic)
                            <tr>
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary me-2">
                                            {{ strtoupper(substr($pic->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $pic->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary" style="font-size: 1rem;">
                                        {{ number_format($pic->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Marketing Point Ranking --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy-fill"></i> Peringkat Point Marketing</span>
                <a href="{{ route('admin.marketings.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($topMarketings->isEmpty() || $topMarketings->sum('total_points') == 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity:.4;"></i>
                    <p class="mb-0 mt-2">Belum ada peringkat</p>
                    <small>Point akan muncul setelah Marketing menyelesaikan tugas</small>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">Rank</th>
                                <th>Nama Marketing</th>
                                <th class="text-center">Total Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topMarketings->filter(fn($m) => ($m->total_points ?? 0) > 0) as $index => $marketing)
                            <tr>
                                <td class="text-center">
                                    @if($index == 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 1rem;">
                                            <i class="bi bi-trophy-fill"></i> 1
                                        </span>
                                    @elseif($index == 1)
                                        <span class="badge bg-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-award-fill"></i> 2
                                        </span>
                                    @elseif($index == 2)
                                        <span class="badge bg-danger" style="font-size: 1rem;">
                                            <i class="bi bi-award"></i> 3
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-success me-2">
                                            {{ strtoupper(substr($marketing->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $marketing->name }}</strong>
                                            @if($marketing->email)
                                                <br><small class="text-muted">{{ $marketing->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success" style="font-size: 1rem;">
                                        {{ number_format($marketing->total_points ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
}
</style>
