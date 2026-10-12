# 🥐 SweetBakes: Artisan Pastry & Cake Studio Manager

<div align="center">

![PHP Native](https://img.shields.io/badge/PHP-8.x%20Native-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL PDO](https://img.shields.io/badge/MySQL-PDO%20Secure-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![CSS3 Flexbox](https://img.shields.io/badge/CSS3-Flexbox%20Pure-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![Security Passed](https://img.shields.io/badge/Security-Anti--SQLi%20%7C%20CSRF%20%7C%20XSS-success?style=for-the-badge&logo=securityscorecard&logoColor=white)

**Sistem Manajemen Etalase & Inventaris Pastry, Artisan Cake, dan Roti Berbasis Web (PHP Native & MySQL).**

</div>

---

| Identitas | Keterangan |
| :--- | :--- |
| **Nama Mahasiswa** | **Nadya Salsabila Zahra** |
| **NIM** | **250180031** |
| **Mata Kuliah** | Pemrograman Web (Pertemuan 3 — Mini Project CRUD) |
| **Tema Proyek** | **SweetBakes — Artisan Pastry & Cake Studio Manager** 🍰 |
| **Arsitektur Utama** | Server-Side Security, PDO Prepared Statements, Pola PRG, Anti-CSRF, Anti-XSS, Box Model & Flexbox |
| **Engine** | PHP Native + MySQL via PDO (Pure Native tanpa Framework) |

---

## 🎯 1. Ikhtisar & Tujuan Proyek

Proyek ini bertujuan untuk merancang dan mengimplementasikan **Sistem Manajemen Inventaris Produk** berbasis server-side murni menggunakan PHP Native dan basis data MySQL. Dengan mengangkat tema **SweetBakes — Artisan Bakery & Pastry Studio**, aplikasi ini mensimulasikan operasional dapur dan etalase toko kue yang mengelola kategori menu seperti:
- `Pastry` (Croissant, Pain au Chocolat, Danish)
- `Artisan Cake` (Cheesecake, Fudge Cake, Tiramisu)
- `Cupcake & Muffin` (Red Velvet, Blueberry Crumble)
- `Cookies & Treats` (Soft Cookies, Macaron)
- `Bread & Loaf` (Sourdough, Brioche)

Aplikasi dibangun dengan prinsip **pertahanan berlapis (*defense-in-depth*)**:
1. **Pencegahan SQL Injection:** Memisahkan instruksi SQL dan data pengguna dengan **PDO Prepared Statements** berparameter.
2. **Pencegahan XSS (Cross-Site Scripting):** Menerapkan fungsi helper `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` pada seluruh output HTML.
3. **Pencegahan CSRF (Cross-Site Request Forgery):** Operasi destruktif (Delete) dilindungi token kriptografik acak `random_bytes(32)` dan diverifikasi melalui `hash_equals()`.
4. **Pencegahan Duplikasi Form (PRG Pattern):** Seluruh request POST dialihkan via HTTP 302/303 Redirect ke halaman GET sehingga refresh browser (F5) tidak menduplikasi data.

---

## 📁 2. Struktur Direktori Proyek

```text
SweetBakes/
├── config/
│   └── db.php               # Konfigurasi koneksi PDO, session, token CSRF, & helper keamanan
├── database/
│   └── store_db.sql         # Skema tabel MySQL & data seed menu awal
├── assets/
│   └── style.css            # Desain tata letak Box Model murni & Flexbox responsif (Dual Theme)
├── index.php                # READ: Etalase menu, Dashboard Metrics, Search & Filter GET, Flash Alert
├── create.php               # CREATE: Formulir registrasi menu baru + validasi server + pola PRG
├── edit.php                 # UPDATE: Formulir edit menu pre-filled + validasi + PRG
├── delete.php               # DELETE: Handler hapus menu aman (Method POST + verifikasi CSRF)
├── run_tests.php            # Automated Test Suite (Pengujian kasus normal & abnormal)
├── jalankan_server.bat      # Launcher 1-klik untuk pengujian instan di Windows
└── README.md                # Dokumentasi sistem, matriks pengujian, & refleksi keamanan
```

---

## 🚀 3. Panduan Instalasi & Menjalankan Aplikasi

### Opsi A: Menggunakan XAMPP (Apache & MySQL)
1. Salin folder proyek ke direktori `htdocs`:
   ```text
   C:\xampp\htdocs\Praktikum3_250180031_Nadya_Salsabila_Zahra
   ```
2. Buka **XAMPP Control Panel**, lalu aktifkan **Apache** dan **MySQL**.
3. Buka browser dan akses **phpMyAdmin** di `http://localhost/phpmyadmin`.
4. Buat database baru bernama `store_db` lalu import berkas `database/store_db.sql`.
5. Akses aplikasi melalui browser:
   ```text
   http://localhost/Praktikum3_250180031_Nadya_Salsabila_Zahra/
   ```

### Opsi B: Menggunakan PHP Built-in Server (Instan 1-Klik)
1. Klik ganda file **`jalankan_server.bat`** di dalam folder proyek.
2. Atau jalankan perintah berikut pada terminal:
   ```powershell
   "C:\xampp\php\php.exe" -S localhost:8000
   ```
3. Buka browser pada alamat: `http://localhost:8000`.

---

## 🛡️ 4. Arsitektur Keamanan Aplikasi

| Lapisan Keamanan | Mekanisme | Implementasi Teknis |
| :--- | :--- | :--- |
| **Database Layer** | PDO Prepared Statements | Seluruh kueri interaktif (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) menggunakan binding parameter (`:name`, `:price`, `:q`, dll). |
| **Output Layer** | Anti-XSS Context-Aware Escaping | Semua data dinamis di-escape dengan `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` sebelum disajikan ke DOM HTML. |
| **Destructive Action** | Anti-CSRF Token Validation | Penghapusan item mewajibkan method POST dan diverifikasi menggunakan `hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])`. |
| **Request Lifecycle** | Post/Redirect/Get (PRG) Pattern | Seluruh proses POST diakhiri `header("Location: index.php"); exit;` untuk memutus form resubmission. |
| **Input Validation** | Server-Side Validation Rules | Panjang nama $\ge 3$ karakter, harga $> 0$, stok $\ge 0$ tipe bilangan bulat non-negatif. |
| **Data Integrity** | Exception Handling Duplikasi | Menangani `PDOException` kode `23000` (UNIQUE Constraint) secara elegan tanpa membocorkan pesan galat internal sistem. |

---

## 🧪 5. Matriks Skenario Uji & Verifikasi Penilaian Dosen (Slide 20)

Seluruh skenario pengujian di bawah ini mencerminkan checklist demonstrasi yang dinilai dosen dan telah diuji kelulusannya:

| No | Skenario Pengujian | Aksi & Data Masukan | Perilaku yang Diharapkan | Status |
| :-: | :--- | :--- | :--- | :-: |
| **1** | **Create Sukses** | Nama: `Pistachio Baklava Roll`, Kat: `Pastry`, Harga: `38000`, Stok: `12` | Data tersimpan, redirect ke `index.php`, notifikasi hijau muncul, item tampil di etalase | **LULUS ✅** |
| **2** | **Validasi Nama Pendek** | Nama: `XY` ($< 3$ karakter) | Ditolak oleh server, form tetap tampil, pesan galat *"Nama menu minimal harus 3 karakter"* | **LULUS ✅** |
| **3** | **Validasi Angka Negatif** | Harga: `-15000` atau Stok: `-5` | Ditolak di server dengan pesan *"Harga tidak boleh 0 atau bernilai negatif"* | **LULUS ✅** |
| **4** | **Duplikasi Item Unik** | Memasukkan nama menu yang sudah ada di database | Ditolak secara elegan oleh exception `23000` tanpa *fatal error* | **LULUS ✅** |
| **5** | **Injeksi XSS** | Nama: `<b>Promo</b><script>alert(1)</script>` | Ditampilkan murni sebagai teks literal berkat `htmlspecialchars()`, skrip tidak dieksekusi | **LULUS ✅** |
| **6** | **Pencegahan Data Ganda (PRG)** | Simpan menu baru, lalu tekan **F5 (Refresh)** di browser | Tidak muncul dialog *"Confirm Form Resubmission"*, tidak ada data kembar di database | **LULUS ✅** |
| **7** | **Proteksi GET Delete** | Mengakses langsung `delete.php?id=1` via address bar | Sistem menolak dengan kode HTTP `405 Method Not Allowed`, data tidak terhapus | **LULUS ✅** |
| **8** | **Proteksi Token CSRF** | Menghapus data via POST tanpa token atau token acak palsu | Ditolak dengan kode HTTP `403 Forbidden`, operasi ditolak | **LULUS ✅** |
| **9** | **Pencarian GET Aman** | Mencari kata kunci `"Croissant"` atau `"Cake"` | Kueri berparameter dijalankan dengan aman (`WHERE name LIKE :q OR category LIKE :q`) | **LULUS ✅** |
| **10** | **Responsivitas Flexbox** | Perkecil ukuran jendela browser ke resolusi ponsel (~400px) | Kartu etalase menyusun ulang secara fleksibel menjadi satu kolom rapi (`flex-wrap`) | **LULUS ✅** |

> **Hasil Verifikasi:** **10/10 Skenario LULUS (100% Memenuhi Kriteria Penilaian).**

---

## 🎨 6. Fitur Antarmuka & Desain Modern

- **Dual Theme Switcher:** Dilengkapi tombol mode tampilan **Light Bakery Warm Mode** dan **Dark Cocoa Mode** yang tersimpan di `localStorage`.
- **Dashboard Metrics Bar:** Menampilkan indikator langsung *Total Varian Menu*, *Valuasi Etalase (Rp)*, *Stok Menipis*, dan *Status Proteksi CSRF*.
- **Flexbox Grid Responsif:** Card produk beradaptasi otomatis terhadap berbagai ukuran layar gawai (desktop, tablet, mobile).
- **Badge Kategori & Kode SKU:** Menampilkan kode menu `#SB-000X` serta badge warna kontras per kategori kue.

---

## 📝 7. Refleksi Keamanan Aplikasi Web
> *"Di bagian mana aplikasi paling rentan: input, query, output, atau alur request? Jelaskan kontrol keamanan yang telah Anda implementasikan."*

1. **Titik Query (Dampak Paling Merusak):**
   - Merupakan titik paling berbahaya jika terjadi celah, karena eksploitasi *SQL Injection* dapat mengakibatkan pengambilalihan hak akses database dan kebocoran data menyeluruh.
   - **Kontrol:** Seluruh query diisolasi menggunakan **PDO Prepared Statements** dengan parameter binding statis, meniadakan perangkaian string variabel langsung.
2. **Titik Input (Vektor Penyerangan Terluas):**
   - Titik awal masuknya data yang dapat dimanipulasi dengan mudah melewati form HTML5.
   - **Kontrol:** Menerapkan validasi ketat sisi server (*Server-Side Validation*) berupa pengecekan panjang karakter, tipe data numerik non-negatif, serta whitelist kategori.
3. **Titik Output (Penyebaran Skrip Berbahaya):**
   - Rentan terhadap *Stored Cross-Site Scripting (XSS)* jika input pengguna dicetak mentah-mentah ke halaman browser.
   - **Kontrol:** Menerapkan *Context-Aware Escaping* melalui fungsi `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
4. **Titik Alur Request (Integritas Transaksi):**
   - Rentan terhadap eksekusi tidak sah dari situs pihak ketiga (*CSRF*) dan pengiriman ulang data berulang saat refresh (*Duplicate Form Submission*).
   - **Kontrol:** Penggunaan pola **Post/Redirect/Get (PRG)** dan validasi token anti-CSRF bertipe *cryptographically secure random bytes*.
