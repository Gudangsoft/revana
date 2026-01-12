@extends('layouts.app')

@section('title', ' - Pengelolaan Pengguna')
@section('page-title', 'Pengelolaan Pengguna')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Pengguna</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                    <i class="bi bi-envelope-fill"></i> Kirim Email Massal
                </button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Pengguna
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('admin.users.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau role..." value="{{ $search ?? '' }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Admin</span>
                                @elseif($user->role === 'reviewer')
                                    <span class="badge bg-primary">Reviewer</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mereset password pengguna ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
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
                            <td colspan="6" class="text-center">Tidak ada data pengguna</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="mt-3">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Broadcast Email Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="broadcastModalLabel">
                    <i class="bi bi-envelope-fill me-2"></i>Kirim Email Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Email akan dikirim ke semua pengguna yang memiliki alamat email terdaftar.
                </div>
                <div class="mb-3">
                    <label for="email_subject" class="form-label">Subjek Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="email_subject" value="MARI SEMANGAT BERSAMA SIPERA" required>
                </div>
                <div class="mb-3">
                    <label for="email_message" class="form-label">Pesan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="email_message" rows="5" required>MARI SEMANGAT BERSAMA SIPERA</textarea>
                    <small class="text-muted">Tulis pesan yang akan dikirim ke semua pengguna</small>
                </div>
                <div id="broadcastResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="sendBroadcastEmail">
                    <i class="bi bi-send-fill me-2"></i>Kirim Email
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('sendBroadcastEmail').addEventListener('click', function() {
        const button = this;
        const subject = document.getElementById('email_subject').value;
        const message = document.getElementById('email_message').value;
        const resultDiv = document.getElementById('broadcastResult');
        
        if (!subject || !message) {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Subjek dan pesan harus diisi!</div>';
            return;
        }
        
        if (!confirm('Yakin ingin mengirim email ke semua pengguna?')) {
            return;
        }
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Sedang mengirim email...</div>';
        
        // Send broadcast email
        fetch('{{ route("admin.users.broadcast-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                subject: subject,
                message: message 
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Server error: ' + (text || response.statusText));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + (data.message || 'Gagal mengirim email') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Terjadi kesalahan: ' + error.message + '</div>';
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-send-fill me-2"></i>Kirim Email';
        });
    });
</script>
@endpush
@endsection
