# 📖 Panduan Penggunaan REVANA

## Untuk Admin

### 1. Login
- Buka http://localhost:8000
- Login dengan email: `admin@revana.com`, password: `password`

### 2. Mengelola Jurnal

#### Tambah Jurnal Baru
1. Klik menu **Jurnal** di sidebar
2. Klik tombol **Tambah Jurnal**
3. Isi form:
   - **Judul Jurnal**: Masukkan judul lengkap jurnal
   - **Link Jurnal**: URL ke jurnal online
   - **Akreditasi**: Pilih SINTA 1-6 (point otomatis sesuai akreditasi)
4. Klik **Simpan**

#### Edit/Hapus Jurnal
- Di halaman daftar jurnal, klik icon pensil untuk edit
- Klik icon tempat sampah untuk hapus

### 3. Menugaskan Reviewer

1. Klik menu **Review Assignments**
2. Klik **Tugaskan Reviewer**
3. Pilih jurnal yang akan direview
4. Pilih reviewer yang sesuai
5. Klik **Assign Reviewer**

**Tips:**
- Lihat beban kerja reviewer (jumlah completed reviews)
- Perhatikan total points reviewer untuk melihat performa

### 4. Monitoring Review

#### Melihat Status Review
1. Klik menu **Review Assignments**
2. Status review:
   - 🟡 **PENDING**: Reviewer belum merespon
   - 🔵 **ACCEPTED**: Reviewer telah menerima
   - 🟣 **ON_PROGRESS**: Sedang dikerjakan
   - 🟢 **SUBMITTED**: Hasil telah diupload, perlu validasi
   - ✅ **APPROVED**: Sudah disetujui
   - 🔴 **REJECTED**: Ditolak reviewer
   - ⚪ **REVISION**: Perlu perbaikan

#### Validasi Hasil Review
1. Klik detail review yang statusnya **SUBMITTED**
2. Download dan periksa file hasil review
3. Pilih aksi:
   - **Approve**: Jika hasil bagus (reviewer dapat point)
   - **Request Revision**: Jika perlu perbaikan (beri feedback)

### 5. Mengelola Reward Redemptions

1. Klik menu **Reward Redemptions**
2. Lihat daftar penukaran reward dari reviewer
3. Status penukaran:
   - 🟡 **PENDING**: Menunggu approval
   - 🔵 **APPROVED**: Disetujui, siap diproses
   - 🟢 **COMPLETED**: Selesai
   - 🔴 **REJECTED**: Ditolak

#### Approve Redemption
1. Klik detail redemption
2. Periksa informasi (rekening untuk transfer uang, dll)
3. Klik **Approve** jika valid
4. Setelah transfer selesai, klik **Complete**

#### Reject Redemption
1. Klik **Reject**
2. Masukkan alasan penolakan
3. Point akan dikembalikan ke reviewer

### 6. Mengelola Reviewer

1. Klik menu **Reviewers**
2. Lihat daftar semua reviewer
3. Klik nama reviewer untuk detail:
   - Total points
   - Badge yang dimiliki
   - History review
   - History point

---

## Untuk Reviewer

### 1. Login
- Buka http://localhost:8000
- Login dengan email reviewer Anda

### 2. Dashboard

Di dashboard, Anda akan melihat:
- **Total Points**: Semua point yang pernah didapat
- **Available Points**: Point yang bisa ditukar reward
- **Completed Reviews**: Jumlah review yang selesai
- **Badge**: Achievement yang sudah didapat

### 3. Mengelola Tugas Review

#### Melihat Daftar Tugas
1. Klik menu **My Tasks**
2. Lihat semua tugas yang ditugaskan

#### Accept/Reject Tugas
1. Klik detail tugas
2. Lihat informasi jurnal dan point reward
3. Pilih:
   - **Terima Tugas**: Jika sanggup mengerjakan
   - **Tolak Tugas**: Jika tidak bisa (beri alasan)

### 4. Mengerjakan Review

#### Mulai Review
1. Setelah accept tugas, klik **Mulai Review**
2. Status berubah menjadi **ON_PROGRESS**
3. Download jurnal dari link yang disediakan

#### Upload Hasil Review
1. Setelah selesai review, klik **Upload Hasil Review**
2. Upload file hasil (PDF/DOC/DOCX, max 10MB)
3. Tambahkan catatan jika perlu
4. Klik **Submit Review**

#### Jika Diminta Revisi
- Admin akan memberi feedback
- Lihat feedback di detail tugas
- Upload ulang file yang sudah diperbaiki

### 5. Mendapatkan Points & Badge

#### Points Otomatis
Setelah admin approve review, Anda akan otomatis dapat point:
- 🥇 SINTA 1 = 100 points
- 🥈 SINTA 2 = 80 points
- 🥉 SINTA 3 = 60 points
- 📊 SINTA 4 = 40 points
- 📈 SINTA 5 = 20 points
- 📉 SINTA 6 = 10 points

#### Badge Otomatis
Badge didapat otomatis berdasarkan jumlah review:
- 🌱 **Reviewer Pemula**: 1 review
- ⭐ **Reviewer Aktif**: 10 review
- 🏆 **Reviewer Expert**: 25 review
- 👑 **Reviewer Master**: 50 review

### 6. Menukar Points dengan Reward

1. Klik menu **Rewards**
2. Lihat daftar reward yang tersedia
3. Reward yang bisa ditukar (hijau): point Anda cukup
4. Reward terkunci (abu-abu): point belum cukup

#### Proses Penukaran
1. Klik **Tukar Sekarang** pada reward yang diinginkan
2. Masukkan informasi:
   - Untuk reward uang: nomor rekening
   - Untuk gratis submit: info jurnal
3. Klik **Tukar Sekarang**
4. Points akan langsung terpotong
5. Menunggu approval admin
6. Cek status di **Riwayat Penukaran**

---

## Tips & Best Practices

### Untuk Admin:
- ✅ Validasi hasil review secepat mungkin
- ✅ Berikan feedback yang jelas jika request revision
- ✅ Proses reward redemption dengan cepat
- ✅ Monitor beban kerja reviewer agar merata

### Untuk Reviewer:
- ✅ Accept tugas sesuai kapasitas Anda
- ✅ Review dengan teliti dan berkualitas
- ✅ Upload file sesuai format (PDF/DOC)
- ✅ Tukar point secara bijak
- ✅ Sertakan informasi lengkap saat redeem reward

---

## FAQ

**Q: Berapa lama review harus selesai?**
A: Tidak ada deadline otomatis, tapi usahakan selesai dalam waktu wajar.

**Q: Apakah bisa reject tugas setelah accept?**
A: Tidak. Jika sudah accept, harus diselesaikan. Pertimbangkan baik-baik sebelum accept.

**Q: Kapan point masuk?**
A: Point masuk otomatis setelah admin approve hasil review Anda.

**Q: Apakah point bisa hangus?**
A: Tidak, point tidak ada expired.

**Q: Berapa lama proses reward?**
A: Tergantung admin, biasanya 1-3 hari kerja.

**Q: Apakah bisa cancel redemption?**
A: Tidak bisa. Point langsung terpotong saat redeem.

---

**Selamat menggunakan REVANA! 🚀**
