# Website HKBP Persiapan Resort Volker

Website resmi jemaat: jadwal ibadah, renungan harian, warta jemaat, laporan kas
gereja, galeri kegiatan, dan permohonan penggunaan gedung gereja — dengan panel
admin untuk pengurus.

**Stack:** Laravel 13 · Filament 3 · Tailwind CSS 4 · Vite 8 · MySQL 8+

---

## Menyiapkan database

Aplikasi memakai MySQL. Buat database dan penggunanya sekali saja:

```sql
CREATE DATABASE hkbp_volker
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'hkbp_volker'@'127.0.0.1' IDENTIFIED BY 'kata-sandi-anda';
GRANT ALL PRIVILEGES ON hkbp_volker.* TO 'hkbp_volker'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Lalu isi `DB_*` di `.env` sesuai kredensial di atas. Kolom `utf8mb4` wajib —
nama dan renungan memuat karakter non-ASCII.

### Cadangan dan pemulihan

Ambil cadangan sebelum migrasi, pembersihan data, atau apa pun yang menghapus baris:

```bash
mysqldump -u root -p --single-transaction --routines --triggers \
  --default-character-set=utf8mb4 hkbp_volker > cadangan.sql

# memulihkan
mysql -u root -p hkbp_volker < cadangan.sql
```

`--single-transaction` membuat cadangan konsisten tanpa mengunci tabel.

### Data contoh vs data jemaat

Isi dari seeder bercampur dengan data jemaat yang sungguhan di basis data yang
sama. Baris contoh dikenali dari ketiadaan berkas terunggah (`foto`,
`file_warta` bernilai NULL) dan dari `created_at` saat seeder dijalankan.
**Bersihkan sebelum situs dipakai sungguhan** — laporan kas terutama, karena
angka contoh di sana tidak boleh terbaca sebagai catatan keuangan nyata.

## Menjalankan secara lokal

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed      # membuat data contoh + akun admin
php artisan storage:link        # agar foto & PDF bisa diakses publik

npm run dev                     # terminal 1 — Vite
php artisan serve               # terminal 2 — aplikasi
```

Akun admin dari seeder: `admin@hkbpvolker.test` / `password`
(**ganti sebelum dipakai sungguhan**). Panel admin ada di `/admin`.

Seeder aman dijalankan berulang — semuanya memakai `firstOrCreate`.

## Pengujian

```bash
composer test                    # seluruh suite
./vendor/bin/pint                # format kode
./vendor/bin/phpstan analyse     # analisis statis
npm run build                    # bundel produksi
```

Keempatnya persis yang dijalankan CI (`.github/workflows/ci.yml`) pada tiap push
ke `main` dan tiap pull request. Jalankan semuanya sebelum commit; yang gagal di
CI selalu juga gagal di sini.

Jumlah test sengaja tidak ditulis di sini — angka semacam itu basi tanpa ada yang
menyadarinya. PHPUnit mencetaknya sendiri di baris terakhir.

Secara bawaan test memakai SQLite in-memory agar cepat. Karena aplikasi memuat
SQL mentah yang bergantung dialek (`TIME(...)` pada pemeriksaan bentrok jadwal),
sesekali jalankan juga suite yang sama di MySQL:

```bash
DB_CONNECTION=mysql DB_DATABASE=hkbp_volker_test \
DB_USERNAME=hkbp_volker DB_PASSWORD='kata-sandi-anda' composer test
```

### Kenapa `composer test`, bukan `php artisan test`

`composer test` memanggil `bin/phpunit.php`. Pada mesin yang ekstensi
**mbstring**-nya tidak bisa dimuat — misalnya diblokir Smart App Control di
Windows dan tidak ada hak admin untuk mematikannya — `vendor/bin/phpunit`
menolak start sama sekali, padahal PHPUnit berjalan baik di atas polyfill.
`bin/phpunit.php` mengulang pemeriksaan ekstensi itu sendiri dan hanya
melonggarkan mbstring, itu pun setelah membuktikan setiap fungsi `mb_*` yang
dibutuhkan tersedia. Ekstensi lain yang hilang tetap menghentikan proses.

Polyfill-nya ada di `bootstrap/polyfill-mbstring.php` (dimuat lewat autoload
`files`), berisi `mb_strimwidth()` dan `mb_strcut()` — dua fungsi yang
dinyatakan "not implemented" oleh `symfony/polyfill-mbstring`, dan
`mb_strimwidth()`-lah yang dipakai `Str::limit()` sehingga tanpanya beranda
membalas 500. Keduanya dijaga `function_exists()`, jadi tidak melakukan apa pun
di mesin yang mbstring-nya sehat.

---

## Struktur yang perlu diketahui

| Berkas | Peran |
|---|---|
| `config/gereja.php` | **Satu sumber kebenaran** untuk nama, alamat, telepon, koordinat, dan menu navigasi. Ubah data gereja di sini, bukan di Blade. |
| `app/Casts/JamHarian.php` | Cast untuk kolom `time`. Menormalkan penyimpanan ke `H:i:s` dan menambatkan jam ke hari ini agar dua jam selalu bisa dibandingkan. |
| `app/Http/Requests/StorePenggunaanGerejaRequest.php` | Validasi permohonan gedung, termasuk pemeriksaan bentrok jadwal. |
| `resources/views/components/` | `layout`, `page-hero`, `section-heading`, `empty-state`, `field`, `kartu-pelayan`, `filter-tahun`. |
| `app/Policies/PengurusPolicy.php` | Dasar perizinan seluruh modul panel. Tiap modul hanya menyebut peran penanggung jawabnya. |
| `presentasi.cmd` / `buka-admin.cmd` | Menyalakan seluruh situs, dan membuka halaman admin. Logikanya di `presentasi.ps1` dan `buka-admin.ps1`. |
| `app/Models/User.php` | `canAccessPanel()` adalah gerbang akses `/admin`. Filament menolak 403 semua orang bila metode ini hilang dan `APP_ENV` bukan `local`. |
| `app/Support/CacheKonten.php` | Cache isi halaman publik, dibatalkan lewat nomor versi. |
| `app/Models/Concerns/` | `MenyegarkanCacheKonten` (batalkan cache saat data berubah) dan `MembersihkanBerkas` (hapus berkas unggahan yang jadi yatim). |

### Navigasi situs

Menu navbar (desktop **dan** mobile) serta menu footer semuanya dibangkitkan dari
`config('gereja.menu')`. Menambah halaman berarti: tambah route bernama, lalu
tambahkan satu baris di config itu — navbar, menu mobile, footer, dan
`sitemap.xml` ikut terbarui sendiri.

### Peran pengurus

Tiga peran, disimpan di kolom `users.role`:

| Peran | Boleh menambah & menyunting | Menghapus |
|---|---|---|
| `admin` | semuanya, termasuk akun pengurus | ya |
| `bendahara` | kas gereja | tidak |
| `sekretaris` | jadwal, renungan, warta, galeri, pelayan, permohonan gedung | tidak |

**Semua peran boleh melihat semua modul.** Menyembunyikan menu dari peran yang
tidak boleh mengubahnya hanya memindahkan pertanyaannya ke grup WhatsApp
pengurus. Yang dibatasi adalah tombol tambah/sunting/hapus.

Penghapusan dikunci ke `admin` karena tidak bisa dibatalkan dan tidak
meninggalkan jejak. Aturannya ada di `app/Policies/PengurusPolicy.php`; tiap
modul hanya menyebut peran penanggung jawabnya.

### Mengganti surel dan kata sandi pengurus

**Lewat panel (cara utama)** — ini satu-satunya cara mengganti **surel**:

1. Masuk ke `/admin` sebagai administrator.
2. Menu **Akun Pengurus** (grup Administrasi).
3. **Edit** pada baris yang ingin diubah.
4. Ubah surel dan/atau isi kolom kata sandi, lalu **Save**.

Kolom kata sandi yang **dibiarkan kosong berarti tidak diubah**, bukan
dikosongkan. Minimal 8 karakter, dan surel tidak boleh sama dengan akun lain.

**Lewat baris perintah** — untuk membuat akun, mengganti peran, dan mengatur
ulang kata sandi (misalnya saat kata sandinya lupa dan panel tidak bisa dibuka):

```bash
php artisan hkbp:akun                       # tanya-jawab
php artisan hkbp:akun ketua@gereja.id --peran=bendahara
```

Perintah ini mengganti kata sandi bila surelnya **sudah** terdaftar, dan membuat
akun baru bila **belum**. Kata sandi sengaja tidak bisa diberikan lewat argumen —
argumen tersimpan di riwayat shell dan terlihat di daftar proses.

> **`hkbp:akun` tidak bisa mengganti surel.** Menjalankannya dengan surel yang
> belum terdaftar akan membuat akun **baru**, bukan mengganti nama akun lama.
> Untuk mengganti surel, pakai panel. Kalau kata sandinya lupa: atur ulang lewat
> perintah ini dulu, masuk ke panel, baru ganti surelnya di sana.

Prosedur di atas dijaga `tests/Feature/GantiAkunPengurusTest.php`.

### Cadangan basis data

`php artisan hkbp:cadangkan` menulis dump ke `storage/app/backups` dan membuang
yang lebih tua dari 14 hari (`--simpan=N` untuk mengubahnya).

Jadwalnya **tiap jam pada menit ke-15**, bukan sekali pada dini hari, dengan
bendera `--sekali-sehari` yang membuat perintahnya langsung keluar bila hari itu
sudah punya cadangan. Hasil akhirnya tetap satu cadangan per hari, tetapi mesin
yang tidak menyala 24 jam — laptop pengembangan, atau server yang sempat mati
semalam — tidak lagi kehilangan cadangan hari itu. Penjadwal Laravel tidak punya
mekanisme menyusul jadwal yang terlewat, jadi inilah penggantinya.

Cadangan manual (tanpa bendera itu) selalu dibuat, berapa pun yang sudah ada
hari itu — misalnya tepat sebelum menjalankan migrasi.

**Di server**, jadwal hanya berjalan bila ada satu entri cron:

```
* * * * * cd /path/ke/hkbp-volker && php artisan schedule:run >> /dev/null 2>&1
```

**Di Windows**, klik dua kali `pasang-jadwal.cmd` di akar proyek (sekali saja).
Itu mendaftarkan tugas terjadwal "HKBP Volker - Penjadwal Laravel" yang
menjalankan `schedule:run` tiap menit secara tersembunyi. `lepas-jadwal.cmd`
melepasnya kembali.

| Berkas | Peran |
|---|---|
| `pasang-jadwal.cmd` / `lepas-jadwal.cmd` | Yang Anda klik. |
| `jadwal-tugas.ps1` | Mendaftar/melepas tugasnya. PowerShell, bukan `schtasks`, karena `schtasks /tr` tidak bisa diandalkan mengutip jalur berspasi. |
| `jadwal-diam.vbs` | Peluncur tanpa jendela. Tanpa ini, jendela konsol berkedip **tiap menit** di layar. |
| `jadwal-laravel.cmd` | Yang benar-benar memanggil `artisan schedule:run`. Berhenti diam-diam bila MySQL mati — di mesin pengembangan itu keadaan normal, bukan kegagalan. |

Catatan penjadwal ada di `storage/logs/jadwal.log`, catatan cadangan di
`storage/logs/cadangan.log`.

### Cadangan pada akun basis data berprivilese terbatas

`mysqldump --single-transaction` sejak MySQL 8.0.32 ikut menjalankan
`FLUSH TABLES`, yang menuntut privilese `RELOAD`/`FLUSH_TABLES` — dan itu lazim
tidak diberikan ke akun aplikasi di shared hosting. Tidak ada flag untuk
mematikan flush tersebut.

Perintahnya karena itu mencoba mode konsisten dulu, lalu **turun-derajat ke
penguncian tabel biasa dengan peringatan yang terlihat** bila privilesenya
kurang. Penulisan tertahan selama dump berjalan; untuk basis data sekecil ini
itu hitungan detik.

### Permohonan penggunaan gedung

Setiap permohonan mendapat kode penelusuran (`WG-XXXXXXXX`) yang dibawa pemohon
ke `/penggunaan-gereja/lacak` untuk melihat statusnya. **Isi kolom "Catatan
untuk Pemohon" di panel terbaca oleh pemohon** di halaman itu — tulis alasan
penolakan yang sopan dan jelas.

Pengurus mendapat notifikasi di lonceng panel Filament saat permohonan masuk.
Salurannya `database` supaya bekerja tanpa SMTP; untuk ikut mengirim surel,
tambahkan `'mail'` pada `via()` di `app/Notifications/PermohonanGedungMasuk.php`
setelah kredensial SMTP gereja terisi.

### Fitur operasional dan keamanan

- **Keamanan Akun** di panel mengaktifkan autentikasi dua langkah (TOTP) dan
  kode pemulihan. Administrator juga dapat mengakhiri semua sesi milik akun
  pengurus dari menu **Akun Pengurus**.
- **Log Aktivitas** mencatat perubahan data penting tanpa menyimpan kata sandi,
  rahasia autentikasi dua langkah, atau token sesi.
- **Periode Kas** mengunci transaksi lama dan mendukung saldo awal. Laporan kas
  dapat dicetak/disimpan sebagai PDF dari browser atau diekspor sebagai CSV.
- Bukti transaksi kas disimpan secara privat, bukan di direktori publik.
- Foto galeri, renungan, dan parhalado baru otomatis diperkecil, dikonversi ke
  WebP, dan dibuatkan gambar mini. Foto lama dapat diperiksa lebih dulu dengan
  `php artisan hkbp:optimalkan-gambar --dry-run`.
- Situs publik dapat dipasang sebagai aplikasi (PWA), mempunyai halaman luring,
  pencarian warta/galeri/arsip renungan, serta kalender iCalendar penggunaan
  gedung di `/penggunaan-gereja/kalender.ics`.
- **Agenda Gereja** tersedia di `/agenda`, termasuk unduhan kalender iCalendar
  agar jadwal ibadah dapat disimpan ke aplikasi kalender jemaat.
- **Pengumuman Penting** dapat dijadwalkan dari panel dan otomatis berhenti
  tampil pada waktu akhir yang ditentukan.
- **Permohonan Doa** di `/doa` adalah formulir privat. Pesan tidak dicatat di
  log aktivitas dan hanya dapat dibuka administrator atau sekretaris melalui
  panel pengurus.

Perintah pemeliharaan tambahan:

```bash
php artisan hkbp:periksa-cadangan
php artisan hkbp:pulihkan nama-cadangan.sql
php artisan hkbp:kedaluwarsakan-permohonan
php artisan hkbp:optimalkan-gambar --dry-run
```

Pemulihan basis data bersifat destruktif dan meminta konfirmasi. Gunakan hanya
terhadap berkas di `storage/app/backups`; perintah akan memvalidasi berkas dan
membersihkan tabel lama sebelum mengimpor cadangan secara utuh.

### Cache isi halaman publik

Halaman publik dibaca jauh lebih sering daripada diubah, jadi hasil query-nya
disimpan di cache lewat `App\Support\CacheKonten`. Beranda yang tadinya menembak
enam query per kunjungan kini nol query pada kunjungan berikutnya.

Pembatalannya memakai **nomor versi**, bukan penghapusan kunci satu per satu:
setiap model isi memakai trait `MenyegarkanCacheKonten`, yang menaikkan satu
penghitung pada event `saved` dan `deleted`. Semua kunci lama otomatis tidak
terpakai lagi. Konsekuensinya:

- Perubahan dari panel admin **langsung** terlihat di situs; tidak perlu menunggu
  TTL habis atau menjalankan `php artisan cache:clear`.
- Data yang diubah **tanpa** lewat Eloquent (misalnya `UPDATE` langsung di SQL
  atau `mysql` CLI) tidak memicu pembatalan. Setelah itu jalankan
  `php artisan cache:clear`.
- Tanggal hari ini ikut ke dalam kunci, sehingga "jadwal mendatang" berganti
  sendiri saat lewat tengah malam.

Setel `GEREJA_CACHE_KONTEN=false` untuk mematikannya saat menelusuri dugaan data
basi.

**Hanya nilai polos yang boleh masuk cache.** `config('cache.serializable_classes')`
bernilai `false` — bawaan Laravel yang menolak meng-unserialize kelas PHP apa pun
dari cache, sebagai perlindungan terhadap gadget chain bila `APP_KEY` bocor.
Store `database` (dipakai produksi), `file`, dan `redis` semuanya menegakkannya.

Menyimpan Eloquent Collection langsung ke cache karena itu **tidak bekerja**:
yang kembali `__PHP_Incomplete_Class`, dan halamannya meledak pada kunjungan
KEDUA — yang pertama masih dilayani hasil query segar. Pakailah:

- `CacheKonten::ingatModel($kunci, Model::class, fn () => ...->get())`
- `CacheKonten::ingatHalaman($kunci, Model::class, fn () => ...->paginate())`
- `CacheKonten::ingat()` **hanya** untuk skalar dan array biasa.

Store `array` yang dipakai pengujian tidak menyerialkan sama sekali, sehingga
kesalahan ini tidak terlihat di sana. `tests/Feature/CacheStoreSerialisasiTest.php`
menutup celah itu dengan memaksa store `file` dan memuat tiap halaman dua kali.

### Berkas unggahan

Filament hanya menulis path ke kolom; ia tidak pernah menghapus berkas lamanya.
Trait `MembersihkanBerkas` menutup itu: berkas dibuang saat barisnya dihapus dan
saat kolomnya diganti/dikosongkan, kecuali bila masih ada baris lain yang
menunjuk path yang sama. Model baru yang punya kolom unggahan perlu memakai trait
ini dan mendeklarasikan `kolomBerkas()`.

### Aset

Swiper dan Chart.js diimpor **dinamis**, hanya saat halaman benar-benar memuat
carousel atau grafik. Halaman selain beranda hanya mengunduh ~11 kB JavaScript.
Jangan mengubahnya menjadi impor statis di puncak `resources/js/app.js`.

### Blade

Blade **memotong argumen array multi-baris yang bersarang** pada direktif seperti
`@json(...)` dan `@foreach([...] as $x)`. Rakit array semacam itu di dalam blok
`@php`, lalu berikan variabelnya ke direktif.

---

## Menyalakan website (langkah demi langkah)

### Cara cepat: satu klik

Klik dua kali **`presentasi.cmd`** di akar proyek (`hkbp-volker/`). Ia
mengerjakan keempat langkah di bawah secara berurutan, lalu membuka browser
sendiri ke halaman depan **dan** halaman admin.

```
[1/4] MySQL                  -> dinyalakan bila belum
[2/4] Membangun aset         -> npm run build
[3/4] Server Laravel         -> port 8000
[4/4] Cloudflare Tunnel      -> alamat https publik
```

Alamat publiknya baru diumumkan **setelah dibuktikan bisa dibuka**. Kalau
Cloudflare gagal menerbitkannya (kadang terjadi pada tunnel gratis), skripnya
mengatakan begitu dan menyuruh Anda mengulang — bukan menyerahkan alamat mati
yang baru ketahuan rusak di depan hadirin.

Menutup jendela itu mematikan server dan tunnel sekaligus.

### Cara manual: empat langkah

Kalau ingin menjalankan sendiri, atau salah satu langkah gagal:

| # | Langkah | Perintah | Cara tahu berhasil |
|---|---|---|---|
| 1 | Nyalakan MySQL | klik `start-mysql.cmd` (di folder induk) | tertulis `MySQL siap di 127.0.0.1:3306` |
| 2 | Bangun aset | `npm run build` | muncul daftar berkas di `public/build` |
| 3 | Nyalakan server | `php artisan serve` | tertulis `Server running on http://127.0.0.1:8000` |
| 4 | Buka ke internet | `cloudflared tunnel --url http://127.0.0.1:8000` | muncul `https://....trycloudflare.com` |

Langkah 4 boleh dilewati kalau cukup dibuka di komputer sendiri.

**Langkah 2 sering terlupa.** Berkas di `public/build` dinamai menurut isinya,
dan halaman menunjuk nama hasil build terakhir. Tanpa `npm run build`,
perubahan tampilan tidak muncul sama sekali.

### Membuka halaman admin

Klik dua kali **`buka-admin.cmd`**. Ia memakai alamat publik yang sedang aktif
bila `presentasi.cmd` berjalan, dan jatuh ke `http://127.0.0.1:8000/admin` bila
tidak — menyalakan server lebih dulu kalau perlu.

Alamat publik yang sedang aktif disimpan di `storage/app/alamat-publik.txt`, dan
dihapus lagi saat `presentasi.cmd` ditutup, supaya `buka-admin.cmd` tidak pernah
menawarkan alamat yang sudah mati.

### ⚠️ Sebelum membagikan alamatnya

Tunnel membuat **seluruh situs** bisa dijangkau siapa pun yang punya alamatnya,
termasuk `/admin`. `robots.txt` menutupnya dari mesin pencari, tetapi tidak dari
manusia yang menebak.

Jadi **ganti kata sandi admin dulu** kalau masih memakai bawaan seeder
(`admin@hkbpvolker.test` / `password`):

```bash
php artisan hkbp:akun admin@hkbpvolker.test
```

### Yang perlu diingat soal alamat tunnel

- **Jendela `presentasi.cmd` harus tetap terbuka.** Menutupnya mematikan alamatnya.
- **Alamatnya berganti setiap kali dijalankan ulang.** Jangan dicetak di undangan
  atau slide; sebutkan lisan, atau buat QR code sesaat sebelum mulai.
- Laptop harus menyala dan terhubung internet selama presentasi.
- Cloudflare membatasi pembuatan tunnel gratis bila dibuat berkali-kali dalam
  waktu singkat. Bila beberapa percobaan berturut-turut gagal, tunggu beberapa
  menit sebelum mencoba lagi.

Untuk alamat permanen yang hidup walau laptop mati, situs ini perlu dideploy ke
hosting sungguhan (cPanel, Railway, atau VPS).

### Kenapa situs harus benar di belakang proxy

Tunnel — dan nanti hosting mana pun — meneruskan permintaan ke PHP sebagai
`http` meski pengunjung membukanya lewat `https`. Dua hal yang karena itu perlu
diatur, dan keduanya tidak terlihat rusak sama sekali dari `localhost`:

1. `bootstrap/app.php` memercayai `X-Forwarded-Proto` supaya URL aset ikut
   `https`. Tanpa itu browser memblokirnya sebagai mixed content dan situs
   tampil **tanpa CSS sama sekali**. `X-Forwarded-Host` sengaja **tidak**
   dipercaya — itu vektor pemalsuan host, dan nama host aslinya sudah datang
   lewat header `Host` biasa.

   Nilainya ditulis harfiah, bukan `env()`: `bootstrap/app.php` berjalan sebelum
   `.env` dimuat, jadi `env()` di sana selalu `null` dan pengaturannya diam-diam
   tidak pernah berlaku.

2. `config/filesystems.php` memakai URL relatif `/storage`, bukan `APP_URL`.
   Menempelkan `APP_URL` mengunci seluruh foto ke satu nama host — di host lain
   semuanya menunjuk `http://localhost:8000` yang tidak ada di komputer
   pengunjung. URL absolut yang memang perlu (`og:image`) dijadikan absolut di
   layout.

`tests/Feature/DiBelakangProxyTest.php` menjaga keduanya.

---

## Sebelum ke produksi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` diisi domain sungguhan
- [ ] Buat akun administrator dengan `php artisan hkbp:akun`
      (seeder data contoh menolak berjalan di produksi, jadi akun
      `admin@hkbpvolker.test` / `password` tidak akan ikut terbawa)
- [ ] Pasang entri cron `schedule:run` — tanpa itu cadangan harian tidak jalan
      (di Windows: klik dua kali `pasang-jadwal.cmd`)
- [ ] Isi data gereja yang sebenarnya di `config/gereja.php`
      (alamat, telepon, koordinat peta saat ini masih contoh)
- [ ] `php artisan storage:link` di server
- [ ] `php artisan config:cache route:cache view:cache`
- [ ] `CACHE_STORE` diarahkan ke store yang bertahan antar-request
      (`database` — bawaan proyek ini — atau `redis`; **jangan** `array`,
      karena cache isi halaman publik tidak akan pernah kena)
- [ ] `npm run build`
- [ ] HTTPS aktif — `AppServiceProvider` memaksa skema https saat produksi
- [ ] Aktifkan modul Apache `mod_deflate`, `mod_expires`, `mod_headers`
      (dipakai `public/.htaccess` untuk kompresi, cache, dan header keamanan)
