# Monopoly Indonesia 🏙️

Game Monopoly berbasis web dengan kota-kota Indonesia. Multiplayer realtime — pemain menggunakan HP masing-masing, papan ditampilkan di layar besar.

## 🚀 Cara Menjalankan

### Requirement
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache + mod_rewrite (XAMPP / Laragon)

### Setup
1. Clone repo ke folder `htdocs`:
   ```bash
   git clone https://github.com/Rayhann310/monopoly.git
   ```
2. Buka XAMPP, aktifkan **Apache** dan **MySQL**
3. Buka browser: `http://localhost/monopoly`
4. Database akan dibuat **otomatis** (self-healing)

### Akun Admin
- URL: `http://localhost/monopoly/admin`
- Username: `admin`
- Password: `admin123`

## 🎮 Cara Bermain

1. Buka `http://localhost/monopoly` → halaman **Lobby**
2. Klik **"Buat Permainan Baru"** → isi nama kelompok + nama pemain
3. Board utama tampil di layar besar/proyektor
4. Scan QR Code → pemain buka di HP masing-masing
5. Lempar dadu dari HP → pion otomatis bergerak di board utama

## 🛠️ Teknologi
- **Backend**: PHP 8 MVC (tanpa framework)
- **Database**: MySQL dengan PDO
- **Frontend**: Tailwind CSS CDN, SweetAlert2, Font Awesome
- **Realtime**: AJAX Polling (1 detik)

## 📁 Struktur
```
monopoly/
├── app/
│   ├── controllers/   # Home, Player, Admin, Setup
│   ├── models/        # PlayerModel, BoardModel, SessionModel, SettingsModel
│   ├── views/         # home, player, admin, setup, templates
│   └── core/          # App, Controller, Database (self-healing)
├── assets/            # CSS, JS, webfonts
├── index.php          # Entry point
└── .htaccess          # URL routing
```

## ⚙️ Fitur Admin
- Setting uang awal, pajak, bonus Start
- Kelola kartu Kesempatan & Dana Umum
- Monitor sesi aktif & jumlah pemain
- Self-heal database (perbaiki tabel otomatis)
- Hentikan / hapus sesi permainan

> **Catatan**: Proyek ini adalah PHP murni dan **tidak memerlukan** `npm install`.
