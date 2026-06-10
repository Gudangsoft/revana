@php
    $missingFields = collect();
    if (empty($profileUser->email))         $missingFields->push('Email');
    if (empty($profileUser->tanggal_lahir)) $missingFields->push('Tanggal Lahir');
    $modalId = 'incompleteProfileModal_' . $profileUser->id;
@endphp
@if($missingFields->isNotEmpty())
{{-- Modal Popup Profil Belum Lengkap --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="{{ $modalId }}Label">
                    <i class="bi bi-person-exclamation me-2"></i>Data Profil Belum Lengkap
                </h5>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-2">
                    Data profil Anda yang belum diisi:
                </p>
                <ul class="list-group list-group-flush mb-3">
                    @foreach($missingFields as $field)
                    <li class="list-group-item d-flex align-items-center gap-2 text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                        <div>
                            <strong>{{ $field }}</strong> belum diisi
                            @if($field === 'Email')
                            <div class="small text-muted">Gunakan Gmail atau email aktif yang benar-benar Anda gunakan sehari-hari</div>
                            @endif
                            @if($field === 'Tanggal Lahir')
                            <div class="small text-muted">Digunakan untuk notifikasi ulang tahun dari tim</div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="alert alert-light border-start border-warning border-3 py-2 px-3 mb-0 text-start">
                    <p class="small mb-1"><i class="bi bi-info-circle text-warning me-1"></i><strong>Catatan penting:</strong></p>
                    <ul class="small mb-0 text-muted ps-3">
                        <li>Gunakan <strong>email/Gmail yang aktif</strong> dan rutin Anda cek</li>
                        <li>Notifikasi tugas dan pengumuman dikirim ke email tersebut</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="localStorage.setItem('profile_remind_{{ $profileUser->id }}', Date.now())">
                    <i class="bi bi-clock me-1"></i>Nanti Saja
                </button>
                <a href="{{ $profileRoute }}" class="btn btn-warning text-dark fw-semibold">
                    <i class="bi bi-pencil-square me-1"></i>Lengkapi Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var modalId  = '{{ $modalId }}';
    var storageKey = 'profile_remind_{{ $profileUser->id }}';
    var lastDismiss = localStorage.getItem(storageKey);
    // Tampilkan lagi jika belum pernah dismiss atau sudah lebih dari 1 hari
    var showNow = !lastDismiss || (Date.now() - parseInt(lastDismiss)) > 86400000;
    if (showNow) {
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById(modalId);
            if (el) { new bootstrap.Modal(el).show(); }
        });
    }
})();
</script>
@endif
