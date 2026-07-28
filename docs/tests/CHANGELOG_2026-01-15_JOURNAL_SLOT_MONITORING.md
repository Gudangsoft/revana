# CHANGELOG - Fitur Pemantauan Slot Artikel Jurnal

**Tanggal:** 15 Januari 2026

## 📊 Fitur Baru: Pemantauan Slot Artikel Jurnal

### 1. **Menu Baru di Sidebar**
   - Menu "Kelola Jurnal" dengan accordion/dropdown:
     - Daftar Jurnal
     - Pemantauan Slot (BARU)

### 2. **CRUD Jurnal - Penambahan Field**
   
   **Form Create & Edit Jurnal sekarang mencakup:**
   - ✅ **Volume Terbitan** (required) - Format: "Vol 5 No 1 (2026)"
   - ✅ **Jumlah Slot Artikel** (required) - Jumlah artikel maksimal yang dapat ditampung
   - Judul Jurnal
   - Link Jurnal
   - Akreditasi
   - Publisher/Terbitan
   - Marketing
   - PIC
   - Username & Password Author
   - Link Turnitin

### 3. **Halaman Pemantauan Slot Artikel**
   
   **URL:** `https://portal.apji.org/admin/journals/monitoring`
   
   **Fitur:**
   - **4 Kartu Statistik:**
     - Total Jurnal (Biru)
     - Total Slot (Kuning/Orange)
     - Slot Terpakai (Cyan)
     - Slot Tersedia (Hijau)
   
   - **Tabel Detail Per Jurnal:**
     - Nama Jurnal
     - Volume Terbitan
     - Akreditasi
     - Total Slot
     - Slot Terpakai
     - Slot Tersedia
     - Progress Bar (dengan warna status):
       - Hijau (0-69%): Slot masih banyak tersedia
       - Kuning (70-89%): Hampir penuh
       - Merah (90-100%): Penuh/Kritis
     - Badge Status:
       - ✅ Tersedia (hijau)
       - ⚠️ Hampir Penuh (kuning)
       - 🔴 Penuh (merah)
     - Tombol Edit

### 4. **Update Halaman Daftar Jurnal**
   - Menambahkan tombol "Pemantauan Slot" di header
   - Menambahkan kolom "Volume" dan "Slot" pada tabel

### 5. **Controller Updates**
   
   **JournalController.php:**
   - Method baru: `monitoringSlots()` - Menghitung statistik dan menampilkan monitoring
   - Validasi `store()`: Menambahkan `slot` dan `volume` sebagai field required
   - Validasi `update()`: Menambahkan `slot` dan `volume` sebagai field required

### 6. **Route Baru**
   ```php
   Route::get('/journals/monitoring', [JournalController::class, 'monitoringSlots'])
       ->name('admin.journals.monitoring');
   ```

### 7. **Relasi Database**
   Model Journal sudah memiliki relasi:
   - `assignments()` - HasMany ke ReviewAssignment
   - Digunakan untuk menghitung slot terpakai

## 🎯 Cara Penggunaan

### Admin - Menambah Jurnal Baru:
1. Masuk ke menu "Kelola Jurnal" > "Daftar Jurnal"
2. Klik tombol "Tambah Jurnal"
3. **Isi field wajib:**
   - Volume Terbitan (contoh: "Vol 5 No 1 (2026)")
   - Jumlah Slot Artikel (contoh: 50)
   - Judul Jurnal
   - Link Jurnal
   - Akreditasi
4. Isi field opsional lainnya
5. Klik "Simpan"

### Admin - Memantau Slot Artikel:
1. Masuk ke menu "Kelola Jurnal" > "Pemantauan Slot"
2. Lihat kartu statistik di bagian atas untuk overview
3. Tabel menampilkan detail setiap jurnal:
   - Progress bar menunjukkan persentase penggunaan slot
   - Badge status menunjukkan kondisi slot:
     - **Tersedia**: Slot masih banyak (< 70%)
     - **Hampir Penuh**: Perlu perhatian (70-89%)
     - **Penuh**: Kritis, slot hampir/sudah habis (≥ 90%)
4. Klik tombol edit untuk mengubah data jurnal

### Admin - Mengedit Slot/Volume:
1. Dari halaman monitoring atau daftar jurnal, klik tombol edit (pensil)
2. Ubah volume atau jumlah slot sesuai kebutuhan
3. Klik "Update"

## 📈 Benefit Fitur

1. **Transparansi Kapasitas** - Admin dapat melihat kapasitas jurnal secara real-time
2. **Perencanaan Lebih Baik** - Mengetahui jurnal mana yang masih bisa menerima artikel
3. **Alert Visual** - Warna progress bar dan badge membantu identifikasi cepat
4. **Data Terstruktur** - Volume dan slot artikel tercatat dengan rapi
5. **Monitoring Efisien** - Tidak perlu menghitung manual slot yang tersedia

## 🔄 Integrasi

Fitur ini terintegrasi dengan:
- Review Assignment System (menghitung slot terpakai berdasarkan assignment)
- Model Journal (relasi dengan ReviewAssignment)
- Sidebar Navigation (menu accordion)

## 📝 Catatan

- Field `slot` dan `volume` sekarang **required** (wajib diisi)
- Jurnal lama yang belum memiliki slot/volume perlu diupdate
- Perhitungan slot terpakai berdasarkan jumlah ReviewAssignment per jurnal
- Progress bar otomatis berubah warna sesuai persentase penggunaan

---

**Status:** ✅ Implementasi Selesai
**Testing:** Siap untuk testing di environment lokal
