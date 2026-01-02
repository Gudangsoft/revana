# Login PIC dan Fitur PIC Author

## Akses PIC System

### Login PIC
1. Buka halaman login utama: http://localhost:8000/login
2. Klik link **"Login sebagai PIC"** di bagian bawah form
3. Atau akses langsung: http://localhost:8000/pic/login

### Kredensial Demo PIC
- **PIC Author:**
  - Email: `author@revana.test`
  - Password: `password`

- **PIC Editor:**
  - Email: `editor@revana.test`
  - Password: `password`

- **PIC Reviewer 1:**
  - Email: `reviewer1@revana.test`
  - Password: `password`

- **PIC Reviewer 2:**
  - Email: `reviewer2@revana.test`
  - Password: `password`

## Fitur PIC Author

### Dashboard
Setelah login sebagai PIC Author, Anda akan melihat:
- Daftar semua artikel yang sudah Anda input
- Status setiap artikel (Pending, Processing, Completed)
- Informasi PIC Marketing dan PIC Editor yang ditugaskan
- Tombol untuk input artikel baru

### Input Artikel Baru

#### Form Input meliputi:
1. **Slot Jurnal** (required)
   - Nomor slot jurnal (1, 2, 3, dst.)

2. **Judul Artikel** (required)
   - Judul lengkap artikel yang akan direview

3. **Akreditasi** (required)
   - Pilih dari dropdown akreditasi yang tersedia
   - Menampilkan poin untuk setiap akreditasi

4. **Username Author** (required)
   - Username untuk login author ke sistem jurnal

5. **Password Author** (required)
   - Password untuk login author ke sistem jurnal
   - **CATATAN:** Password disimpan dalam bentuk plain text (tidak dienkripsi) untuk kemudahan akses

6. **PIC Marketing** (required)
   - Pilih PIC Marketing yang akan handle artikel ini
   - Dropdown menampilkan semua Marketing yang aktif

7. **Tugaskan ke PIC Editor** (required)
   - Pilih PIC Editor yang akan menangani artikel ini
   - Dropdown menampilkan semua PIC dengan role "EDITOR 1" yang aktif

### Detail Artikel
Klik tombol "Detail" pada daftar artikel untuk melihat:
- Informasi lengkap artikel
- Username dan password author
- PIC terkait (Author, Marketing, Editor)
- Status review (jika sudah ada reviewer yang ditugaskan)
- Timeline review

## Workflow PIC Author

```
1. Login sebagai PIC Author
2. Klik "Input Artikel Baru"
3. Isi form:
   - Slot jurnal
   - Judul artikel
   - Username & password author
   - Pilih akreditasi
   - Pilih PIC Marketing
   - Tugaskan ke PIC Editor
4. Klik "Simpan dan Tugaskan"
5. Artikel masuk ke dashboard PIC Author
6. PIC Editor akan menerima tugas artikel tersebut
7. Monitor progress artikel melalui dashboard
```

## Keamanan

- Setiap PIC hanya bisa melihat artikel yang dia input (untuk Author)
- Password PIC dienkripsi dengan bcrypt
- Password author disimpan plain text untuk kemudahan akses sistem jurnal
- Session PIC terpisah dari session User biasa

## Menu Navigasi PIC Author

- **Dashboard**: Lihat semua artikel yang sudah diinput
- **Input Artikel Baru**: Form untuk menambah artikel baru
- **Logout**: Keluar dari sistem PIC

## Tips

1. Pastikan data akreditasi sudah tersedia di database
2. Pastikan ada PIC Marketing yang aktif
3. Pastikan ada PIC Editor yang aktif
4. Simpan username dan password author dengan benar
5. Monitor status artikel secara berkala di dashboard
