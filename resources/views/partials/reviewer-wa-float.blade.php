{{-- Tombol WhatsApp melayang (pojok KIRI bawah) untuk reviewer menghubungi
     admin. Di-include dari layouts/app.blade.php dan hanya dirender untuk user
     ber-role 'reviewer'. Nomor diatur admin di /admin/settings ("Nomor
     WhatsApp Admin", key Setting `admin_whatsapp`); kalau kosong → tidak
     dirender. --}}
@php
    $adminWa = preg_replace('/[^0-9]/', '', (string) ($appSettings['admin_whatsapp'] ?? ''));
    if ($adminWa !== '') {
        if (substr($adminWa, 0, 1) === '0') {
            $adminWa = '62' . substr($adminWa, 1);
        } elseif (substr($adminWa, 0, 2) !== '62') {
            $adminWa = '62' . $adminWa;
        }
    }
    $waGreeting = 'Halo Admin, saya ' . (auth()->user()->name ?? 'reviewer') . ' (reviewer). Saya ingin bertanya mengenai ';
@endphp
@if($adminWa !== '')
<a href="https://wa.me/{{ $adminWa }}?text={{ rawurlencode($waGreeting) }}"
   target="_blank" rel="noopener"
   class="reviewer-wa-float"
   aria-label="Hubungi admin via WhatsApp"
   title="Hubungi Admin via WhatsApp">
    <i class="bi bi-whatsapp"></i>
    <span class="reviewer-wa-float-label">Hubungi Admin</span>
</a>
<style>
    .reviewer-wa-float {
        position: fixed;
        left: 22px;
        bottom: 22px;
        z-index: 1050;
        display: flex;
        align-items: center;
        height: 58px;
        padding: 0 17px;
        border-radius: 29px;
        background: #25D366;
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45), 0 2px 6px rgba(0,0,0,0.15);
        text-decoration: none;
        overflow: hidden;
        white-space: nowrap;
        transition: box-shadow .2s ease, transform .2s ease;
        animation: reviewerWaFloat 2.8s ease-in-out infinite;
    }
    .reviewer-wa-float i {
        font-size: 1.7rem;
        line-height: 1;
    }
    .reviewer-wa-float-label {
        max-width: 0;
        opacity: 0;
        font-weight: 600;
        font-size: 0.95rem;
        transition: max-width .25s ease, opacity .2s ease, margin-left .25s ease;
    }
    .reviewer-wa-float:hover,
    .reviewer-wa-float:focus {
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(37, 211, 102, 0.55), 0 3px 8px rgba(0,0,0,0.2);
        animation-play-state: paused;
    }
    .reviewer-wa-float:hover .reviewer-wa-float-label,
    .reviewer-wa-float:focus .reviewer-wa-float-label {
        max-width: 160px;
        opacity: 1;
        margin-left: 10px;
    }
    @keyframes reviewerWaFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-6px); }
    }
    @media (max-width: 576px) {
        .reviewer-wa-float { left: 14px; bottom: 14px; height: 52px; padding: 0 14px; }
        .reviewer-wa-float i { font-size: 1.5rem; }
    }
</style>
@endif
