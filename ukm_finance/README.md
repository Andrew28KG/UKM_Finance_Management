# UKM Finance Management

Aplikasi web untuk mengelola keuangan Unit Kegiatan Mahasiswa (UKM) di kampus. Aplikasi ini memudahkan pencatatan keuangan, meningkatkan transparansi laporan, dan meminimalisir kesalahan pencatatan.

## Fitur Utama

- Pencatatan transaksi keuangan (pemasukan dan pengeluaran)
- Laporan keuangan otomatis dengan visualisasi data
- Manajemen multi-UKM
- Ekspor data ke format XML
- Sistem login dan manajemen pengguna

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

## Instalasi

1. **Clone atau download repository ini**

2. **Import database**
   - Buat database baru di MySQL
   - Import file `database/ukm_finance.sql` ke database yang telah dibuat

3. **Konfigurasi database**
   - Buka file `class/finance.php`
   - Sesuaikan konfigurasi database:
     ```php
     private $host = 'localhost'; // Sesuaikan dengan host database Anda
     private $user = 'root';      // Sesuaikan dengan username database Anda
     private $pass = '';          // Sesuaikan dengan password database Anda
     private $database = "ukm_finance"; // Sesuaikan dengan nama database Anda
     ```

4. **Akses aplikasi**
   - Pindahkan folder `ukm_finance` ke direktori web server Anda
   - Akses melalui browser: `http://localhost/ukm_finance`

## Penggunaan

### Login
- Gunakan email dan password yang telah disediakan:
  - Admin: admin@example.com / password123
  - Bendahara UKM Olahraga: budi@example.com / password123
  - Bendahara UKM Musik: dewi@example.com / password123
  - Bendahara UKM Fotografi: andi@example.com / password123
  - Bendahara UKM Jurnalistik: siti@example.com / password123
  - Bendahara UKM Pecinta Alam: rudi@example.com / password123

### Halaman Dashboard
- Melihat ringkasan keuangan UKM
- Melihat grafik keuangan
- Melihat transaksi terbaru

### Halaman Transaksi
- Menambahkan transaksi baru
- Melihat daftar transaksi
- Mencari dan memfilter transaksi
- Menghapus transaksi

### Halaman Laporan Keuangan
- Melihat laporan keuangan detail
- Melihat grafik distribusi per kategori
- Melihat tren keuangan 6 bulan terakhir
- Mengekspor data ke XML
- Mencetak laporan

## Struktur Folder

- `api/` - Berisi file-file API untuk komunikasi dengan database
- `class/` - Berisi kelas PHP untuk logika bisnis
- `database/` - Berisi file SQL untuk setup database
- `images/` - Berisi gambar dan aset visual
- `inc/` - Berisi file-file include seperti header dan footer
- `js/` - Berisi file JavaScript
- `style/` - Berisi file CSS

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini, silakan fork repository ini dan kirimkan pull request Anda.

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE). 