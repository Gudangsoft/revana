<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Permintaan user 1 Agustus 2026: template email ke penulis (author) belum ada
     * dan tidak bisa dibuat lewat UI — trigger `notify_penulis` sudah lama dipakai
     * kode (SubmissionController::sendPenulisEmail(), dipanggil begitu submission
     * baru dibuat), tapi halaman Template Email Monitoring cuma menampilkan trigger
     * berawalan assign_/validate_, jadi notify_penulis tidak pernah muncul sebagai
     * pilihan (lihat log-update-2026-08-01.md #2 untuk perbaikan UI-nya).
     *
     * Migration ini SENGAJA membuat template dengan is_active=false — supaya admin
     * meninjau dulu redaksi kalimatnya sebelum email mulai terkirim otomatis ke
     * penulis asli (deploy tidak boleh diam-diam mengubah perilaku pengiriman email
     * production). Aktifkan lewat tombol toggle di /admin/email-templates.
     */
    public function up(): void
    {
        EmailTemplate::firstOrCreate(
            ['trigger_key' => 'notify_penulis'],
            [
                'name' => 'Notifikasi ke Penulis (Submission Diterima)',
                'subject' => '[{app_name}] Artikel Anda Telah Diterima Sistem – {nama_artikel}',
                'body' => <<<'HTML'
Yth. {nama_penulis},<br><br>

Terima kasih telah mengirimkan artikel Anda ke <strong>{nama_jurnal}</strong>. Artikel Anda telah kami terima dan tercatat dalam sistem dengan detail sebagai berikut:<br><br>

<ul>
<li><strong>Judul Artikel</strong>: {nama_artikel}</li>
<li><strong>Kode Submit</strong>: {kode_submit}</li>
<li><strong>ID Artikel</strong>: {id_artikel}</li>
<li><strong>Jurnal Tujuan</strong>: {nama_jurnal}</li>
</ul>

Berikut akun Anda untuk memantau status artikel di website jurnal ({url_jurnal}):<br>
<ul>
<li><strong>Username</strong>: {username_author}</li>
<li><strong>Password</strong>: {password_author}</li>
</ul>

Artikel Anda akan segera diproses melalui tahapan editorial. Kami akan menginformasikan perkembangan selanjutnya melalui email ini.<br><br>

Terima kasih atas kepercayaan Anda.<br><br>
Salam,<br>
Tim {app_name}<br>
<small style="color:#888;">Email ini dikirim otomatis oleh sistem pada {tanggal}.</small>
HTML,
                'is_active' => false,
            ]
        );
    }

    public function down(): void
    {
        EmailTemplate::where('trigger_key', 'notify_penulis')->delete();
    }
};
