# DINASGO - Sistem Manajemen Perjalanan Dinas

Aplikasi web untuk mengelola pengajuan, proses, dan evaluasi perjalanan dinas karyawan secara terintegrasi dan efisien.

## 📋 Deskripsi Proyek

DINASGO adalah sistem informasi yang dirancang untuk memudahkan proses administrasi perjalanan dinas di level organisasi pemerintah/swasta. Aplikasi ini memungkinkan karyawan untuk mengajukan perjalanan dinas, mengunggah dokumen pendukung, dan memantau status pengajuan mereka secara real-time.

## 🎯 Fitur Utama

-   **Manajemen User**: Sistem login dengan role-based access (Admin, Atasan, Bendahara, Pegawai)
-   **Pengajuan Perjalanan**: Pegawai dapat membuat pengajuan perjalanan dinas dengan detail lengkap
-   **Manajemen Dokumen**: Unggah dan kelola dokumen (SPPD, bukti pengeluaran, surat tugas, dll)
-   **Dashboard**: Monitoring dan tracking status pengajuan secara real-time
-   **Evaluasi Perjalanan**: Fitur evaluasi pasca-perjalanan dengan penilaian dan rekomendasi
-   **Rincian Biaya**: Perhitungan dan pencatatan detail biaya perjalanan dinas

## 🛠️ Teknologi yang Digunakan

-   **Backend**: PHP (Native)
-   **Database**: MySQL
-   **Frontend**: HTML, CSS, JavaScript
-   **Template Engine**: FPDF untuk generate dokumen PDF

## 📁 Struktur Folder

```
dinasgo/
├── auth/              # Modul autentikasi (login, logout, session)
├── config/            # Konfigurasi dan koneksi database
├── database/          # File SQL untuk setup database
├── modules/           # Module aplikasi berdasarkan role user
│   ├── admin/         # Admin dashboard
│   ├── atasan/        # Atasan/supervisor dashboard
│   ├── bendahara/     # Bendahara dashboard
│   ├── pegawai/       # Pegawai dashboard
│   └── shared/        # Komponen bersama
├── fpdf/              # Library FPDF untuk generate PDF
├── assets/            # Static files (CSS, JS, images)
├── html/              # Template HTML
├── layouts/           # Layout templates
└── uploads/           # Folder untuk menyimpan file upload
```

## 🚀 Cara Menjalankan

### Prasyarat

-   PHP >= 7.4
-   MySQL/MariaDB
-   Web Server (Apache/Nginx)

### Instalasi

1. **Clone atau download project ke folder web root**

    ```bash
    git clone <repository> dinasgo
    cd dinasgo
    ```

2. **Buat database**

    ```bash
    mysql -u root -p < database/dinasgo.sql
    ```

3. **Konfigurasi koneksi database**

    - Edit file `config/koneksi.php`
    - Sesuaikan host, user, password, dan nama database

4. **Akses aplikasi**
    - Buka browser: `http://localhost/dinasgo`
    - Default redirect ke halaman login

### Akun Login Default

Lihat file `database/dinasgo.sql` untuk melihat seed data user

## 👤 Developer

**M.Rezqy Noor Ridha**

## 📚 Kontak & Support

Untuk pertanyaan atau masalah terkait aplikasi, silakan hubungi developer.

---

**Last Update**: Desember 2025
