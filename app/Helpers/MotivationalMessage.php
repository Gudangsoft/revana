<?php

namespace App\Helpers;

class MotivationalMessage
{
    private static array $messages = [
        '💪 Semangat bekerja hari ini! Setiap langkah kecil membawa perubahan besar.',
        '🌟 Anda adalah bagian penting dari tim ini. Terus bersinar!',
        '🚀 Hari baru, semangat baru. Jadikan hari ini lebih produktif dari kemarin!',
        '🎯 Fokus pada tujuan, bukan hambatan. Anda pasti bisa!',
        '✨ Kerja keras hari ini adalah kesuksesan hari esok.',
        '🌈 Setiap pekerjaan yang diselesaikan adalah sebuah prestasi. Banggalah!',
        '💡 Ide terbaik lahir dari semangat yang tak pernah padam.',
        '🏆 Juara bukan yang tidak pernah jatuh, tapi yang selalu bangkit.',
        '🌺 Mulai hari dengan senyum dan semangat. Dunia akan ikut tersenyum!',
        '⚡ Energi positifmu menular ke seluruh tim. Teruskan!',
        '🎊 Anda sudah hadir — itulah langkah pertama menuju sukses hari ini!',
        '🌻 Teruslah tumbuh, teruslah berkembang. Potensimu tidak terbatas!',
        '💎 Kerja berkualitas menghasilkan hasil yang luar biasa.',
        '🦋 Perubahan besar dimulai dari tindakan kecil yang konsisten.',
        '🔥 Semangat tidak padam oleh kesulitan — justru semakin menyala!',
        '🌊 Seperti ombak, terus bergerak maju tidak peduli rintangan.',
        '🦅 Terbang tinggi dengan kerja keras dan dedikasi penuh.',
        '🍀 Keberuntungan berpihak pada mereka yang bekerja keras dan pantang menyerah.',
        '🎯 Satu tugas selesai hari ini adalah investasi nyata untuk masa depan!',
        '💫 Sekecil apapun usahamu, dampaknya bisa jauh lebih besar dari yang kamu bayangkan.',
        '🏅 Jadilah versi terbaik dirimu hari ini — karena kemarin sudah berlalu!',
        '🌸 Ketulusan dalam bekerja adalah kunci kepuasan sejati.',
        '🎵 Ritme kerja yang baik menghasilkan harmoni tim yang indah.',
        '🌙 Tantangan hari ini menempa dirimu menjadi lebih kuat.',
        '🌝 Selamat bekerja! Kontribusi Anda sangat berarti bagi tim dan organisasi.',
        '🙌 Tim yang solid dimulai dari individu yang berdedikasi — terima kasih sudah ada!',
        '📈 Setiap data yang diinput, setiap proses yang diselesaikan — semuanya penting!',
        '🎖️ Konsistensi adalah senjata paling ampuh menuju puncak.',
        '🌅 Setiap pagi adalah kesempatan baru untuk membuat perbedaan.',
        '🤝 Bersama kita kuat — semangat berkolaborasi hari ini!',
    ];

    public static function random(): string
    {
        return self::$messages[array_rand(self::$messages)];
    }
}
