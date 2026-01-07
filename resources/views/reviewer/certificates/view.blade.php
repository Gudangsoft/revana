@extends('layouts.app')

@section('title', ' - Preview Sertifikat')
@section('page-title', 'Preview Sertifikat')

@section('sidebar')
    @include('reviewer.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-eye"></i> Preview Sertifikat</span>
                <a href="{{ route('reviewer.certificates.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5>Detail Review:</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Artikel:</th>
                                <td>{{ $assignment->article_title }}</td>
                            </tr>
                            <tr>
                                <th>Nomor Artikel:</th>
                                <td><span class="badge bg-primary">{{ $assignment->article_number }}</span></td>
                            </tr>
                            <tr>
                                <th>Bahasa:</th>
                                <td><span class="badge bg-secondary">{{ $assignment->language }}</span></td>
                            </tr>
                            <tr>
                                <th>Tanggal Approved:</th>
                                <td>{{ $assignment->approved_at ? $assignment->approved_at->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Posisi:</th>
                                <td>
                                    @if($assignment->reviewer_id == auth()->id())
                                        <span class="badge bg-info">Reviewer 1</span>
                                    @elseif($assignment->reviewer_2_id == auth()->id())
                                        <span class="badge bg-info">Reviewer 2</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="text-center mb-3">
                    <h5><i class="bi bi-award-fill text-warning"></i> Preview Sertifikat</h5>
                    <p class="text-muted">Klik tombol download di bawah untuk mengunduh sertifikat dengan kualitas penuh</p>
                </div>

                <div class="text-center mb-4">
                    <img src="{{ asset($certificatePath) }}" 
                         class="img-fluid border rounded shadow" 
                         alt="Preview Sertifikat"
                         style="max-width: 100%; height: auto;">
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('reviewer.certificates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('reviewer.certificates.download', $assignment) }}" class="btn btn-success btn-lg">
                        <i class="bi bi-download"></i> Download Sertifikat (Kualitas Penuh)
                    </a>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Info:</strong> Preview ini ditampilkan dengan kualitas standar. 
                    File yang didownload akan memiliki kualitas lebih tinggi dan cocok untuk dicetak.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto cleanup preview file after 5 minutes
setTimeout(function() {
    fetch('{{ route("reviewer.certificates.index") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'cleanup_preview',
            path: '{{ $certificatePath }}'
        })
    });
}, 300000); // 5 minutes
</script>
@endsection
