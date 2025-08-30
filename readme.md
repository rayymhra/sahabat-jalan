# 🚦 GoSafe (Sahabat Jalan)

**GoSafe** adalah platform berbasis komunitas yang memungkinkan pengguna untuk melaporkan dan berbagi informasi mengenai kondisi keselamatan jalan di Indonesia. Sistem ini menyediakan **peta interaktif** yang menampilkan laporan pengguna, seperti area rawan kejahatan, kecelakaan, bahaya, hingga titik aman.

---

## ✨ Fitur Utama

- 🗺️ **Peta Interaktif** - Menampilkan laporan dari pengguna
- 🚨 **Laporan Insiden** - Kejahatan, kecelakaan, bahaya jalanan
- ✅ **Titik Aman** - Lokasi-lokasi yang direkomendasikan
- 👤 **Manajemen Akun** - Registrasi, login, dan edit profil
- 🛣️ **Manajemen Rute** - Tambah rute dan laporan baru
- 💬 **Interaksi Komunitas** - Komentar dan interaksi antar pengguna

---

## ⚙️ Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/username/sahabat-jalan.git
cd sahabat-jalan
```

### 2. Setup Database
1. Buka phpMyAdmin
2. Buat database baru bernama `jalan_aman`
3. Import file `jalan_aman.sql` yang ada dalam folder repository

### 3. Pindahkan Folder Project
- **Laragon**: Taruh di folder `www`
- **XAMPP**: Taruh di folder `htdocs`

### 4. Jalankan Project
Buka browser dan akses:
```
http://localhost/sahabat-jalan/
```

---

## 🚀 Cara Menggunakan

### 📍 Landing Page
- Klik tombol **"Lihat Peta Sekarang"** untuk masuk ke halaman utama (peta & laporan)

### 🔐 Login / Daftar
- Klik **Masuk** atau **Daftar** di navbar
- **Akun demo** yang tersedia:
  - Email: `user1@gmail.com`
  - Password: `123`
- Atau buat akun baru melalui menu **Daftar**

### 🛣️ Menambah Rute Baru
1. Klik tombol **Rute** (hijau)
2. Modal "Buat Rute Baru" akan muncul
3. Klik **Pilih Titik Awal** → pilih lokasi di peta
4. Klik **Pilih Titik Akhir** → pilih lokasi di peta
5. Klik **Buat Rute** → isi form yang diperlukan
6. Tambahkan laporan sesuai kebutuhan, lalu klik **Selesai**

### 📝 Melihat & Menambah Laporan
- Klik rute yang sudah ada di peta untuk melihat laporan terkait
- Tambahkan laporan baru pada rute tersebut

### 👤 Profil Pengguna
- Lihat profil pengguna lain
- Edit profil sendiri
- Kelola laporan, rute, dan komentar yang sudah dibuat (bisa dihapus)

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP (Native)
- **Database**: MySQL
- **Peta Interaktif**: Leaflet.js
- **Frontend**: HTML, CSS, JavaScript, Bootstrap

---

## 👥 Kontribusi

Kontribusi sangat terbuka! Silakan:
1. Fork repository ini
2. Buat branch baru
3. Kirim pull request

---

## 📜 Lisensi

Proyek ini dibuat untuk tujuan pembelajaran. Silakan gunakan, modifikasi, dan kembangkan lebih lanjut.