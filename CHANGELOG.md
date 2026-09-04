# Catatan Perubahan

Semua perubahan yang berarti bagi pengurus dan jemaat dicatat di berkas ini.

Formatnya mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/).

## Cara penomorannya

`MAYOR.MINOR.TAMBALAN`, dengan arti yang disesuaikan untuk sebuah situs gereja —
bukan untuk pustaka yang dipakai program lain:

| Bagian | Naik ketika |
|---|---|
| **MAYOR** | Situs sudah dipakai sungguhan, atau ada perubahan yang menuntut pengurus mengerjakan sesuatu (migrasi data, mengubah `.env`, memasang cron baru). |
| **MINOR** | Ada modul atau halaman baru. |
| **TAMBALAN** | Perbaikan galat, keamanan, dan penyuntingan teks. |

Versi masih `0.x` selama situs belum pernah naik ke hosting sungguhan.
**`1.0.0` ditandai pada hari situs benar-benar dipakai jemaat** — bukan pada hari
fiturnya dianggap lengkap.

---

## [Belum dirilis]

### Ditambahkan

- **Permohonan doa memberi tahu pengurus.** Pokok doa yang masuk kini mengisi
  lonceng panel administrator dan sekretaris — sebelumnya ia hanya terbaca bila
  seseorang kebetulan membuka menunya. Notifikasinya **tidak membawa isi doa
  maupun nama pengirim**: halaman `/doa` menjanjikan formulir privat, dan baris
  notifikasi adalah salinan kedua yang tidak dijaga policy mana pun.
- **Pengingat mengabari pemohon gedung.** Begitu status permohonan diubah — dari
  tombol Setujui/Tolak maupun dari formulir suntingnya — muncul ajakan mengirim
  kabarnya lewat WhatsApp, lengkap dengan tombolnya. Kontak yang bukan nomor
  telepon diberitahukan secara terpisah alih-alih dilewati diam-diam.

### Diperbaiki

- **Halaman luring tampil tanpa CSS.** Service worker melayani permintaan aset
  dari cache tetapi tidak pernah menuliskan apa pun ke sana selain HTML, jadi
  halaman yang tersimpan terbuka tanpa gaya sama sekali saat benar-benar luring.
  Berkas gaya, skrip, font, dan gambar sesama asal kini ikut tersimpan.
- **Nama cache service worker mengikuti nomor rilis.** Sebelumnya tetap
  `hkbp-volker-v1` selamanya, sehingga aset lama terus dilayani setelah situs
  naik versi. `VERSI` di `public/sw.js` harus dinaikkan bersama versi di berkas
  ini — dijaga `tests/Feature/PwaLuringTest.php`.

### Diubah

- **Ekspor CSV laporan kas dialirkan, bukan dikumpulkan.** Seluruh transaksi
  dalam rentang sebelumnya dimuat ke memori sebelum baris pertama ditulis.
  Angka ringkasannya kini dijumlahkan di SQL dan barisnya mengalir 500 sekaligus;
  urutan menurut tanggal tidak berubah.

## [0.9.0] — 2026-09-02

Rilis bertanda pertama. Merangkum seluruh riwayat sejak proyek dimulai;
rinciannya per commit ada di `git log`.

### Keamanan

- **Kode TOTP kini sekali pakai.** Sebelumnya satu kode tetap sah sekitar 90
  detik (jendela toleransi ±1 langkah), sehingga kode yang terbaca dari balik
  bahu atau tertinggal di layar yang tidak terkunci masih dapat dipakai orang
  lain selama sisa jendela itu. Pembatasan laju hanya memperlambat penebakan;
  ia tidak menghalangi pemakaian ulang kode yang memang benar. Disyaratkan
  RFC 6238 §5.2. Berlaku di keempat tempat kode diminta: tantangan masuk,
  pengaktifan, penonaktifan, dan penerbitan ulang kode pemulihan.
- **HSTS ikut dipasang `SecurityHeaders`**, bukan hanya `public/.htaccess` yang
  hanya terbaca oleh Apache. Situs yang dilayani nginx, Caddy, atau Railway kini
  ikut terlindungi. Hanya diterbitkan saat `APP_ENV=production` dan permintaannya
  benar-benar https — memasangnya di lokal akan mengunci `localhost` ke https di
  browser pengembang selama setahun.
- **`SESSION_SECURE_COOKIE` didokumentasikan** di `.env.example` dan daftar
  periksa produksi. Tanpa disetel `true`, cookie sesi tidak bertanda `Secure` dan
  ikut terkirim pada permintaan http mana pun ke domain yang sama. Ini tidak
  dapat dinyalakan di lokal, jadi tidak ada yang menyadarinya sampai situs naik.
- Autentikasi dua langkah (TOTP) beserta kode pemulihan dan halaman tantangan.
- Administrator dapat mengakhiri semua sesi milik akun pengurus.
- Log aktivitas untuk perubahan data penting — tanpa menyimpan kata sandi,
  rahasia dua langkah, atau token sesi.
- Bukti transaksi kas disimpan privat, bukan di direktori publik.
- Honeypot dan pembatasan laju pada seluruh formulir publik.
- `X-Forwarded-Host` sengaja tidak dipercaya; sisanya dipercaya agar situs benar
  di belakang proxy TLS.

### Ditambahkan

- **Situs publik sembilan halaman**: beranda, profil, pelayan, renungan beserta
  arsipnya, agenda, warta, galeri, permohonan doa, dan penggunaan gedung gereja.
- **Panel pengurus** (Filament) dengan 12 modul dalam lima grup navigasi, dan
  tiga peran: administrator, bendahara, sekretaris.
- **Permohonan penggunaan gedung** dengan kode penelusuran `WG-XXXXXXXX`,
  pemeriksaan bentrok jadwal, riwayat status, dan notifikasi lonceng ke pengurus.
- **Permohonan doa** sebagai formulir privat; isinya tidak dicatat di log
  aktivitas dan hanya terbuka bagi administrator dan sekretaris.
- **Periode kas** dengan saldo awal dan penguncian transaksi lama. Laporan dapat
  dicetak sebagai PDF dari browser dan diekspor sebagai CSV.
- **Pengumuman penting** terjadwal, berhenti tampil sendiri pada waktu akhirnya.
- **PWA**: situs dapat dipasang sebagai aplikasi, punya halaman luring, dan
  menyediakan kalender iCalendar untuk agenda ibadah dan penggunaan gedung.
- **Pencarian** warta, galeri, dan arsip renungan.
- **Cadangan basis data terjadwal** beserta pemantauan, pemulihan, dan
  penyimpanan kedua yang opsional.
- **Optimasi gambar otomatis** — WebP, gambar mini, dan batas piksel yang
  diturunkan dari `memory_limit` sehingga gambar raksasa dilewati alih-alih
  menjatuhkan proses.
- **CI GitHub Actions**: Pint, Larastan, PHPUnit, dan build aset pada tiap push
  dan pull request.
- Perintah pemeliharaan `hkbp:akun`, `hkbp:cadangkan`, `hkbp:pulihkan`,
  `hkbp:periksa-cadangan`, `hkbp:kedaluwarsakan-permohonan`, dan
  `hkbp:optimalkan-gambar`.
- Skrip sekali klik untuk presentasi (`presentasi.cmd`, `buka-admin.cmd`) dan
  penjadwal Windows (`pasang-jadwal.cmd`).

### Diubah

- Data gereja dipusatkan di `config/gereja.php` — navbar, menu mobile, footer,
  dan `sitemap.xml` semuanya dibangkitkan dari sana.
- Isi halaman publik disimpan di cache dengan pembatalan berbasis nomor versi;
  beranda yang tadinya menembak enam query kini nol query pada kunjungan
  berikutnya.
- Beranda memperlihatkan tren kas 12 bulan terakhir, bukan lagi total sepanjang
  masa yang makin lama makin tidak berarti.
- Swiper dan Chart.js diimpor dinamis; halaman selain beranda hanya mengunduh
  ~11 kB JavaScript.

### Diperbaiki

- **Saldo awal laporan kas untuk rentang yang tidak mulai tanggal 1.** Versi
  sebelumnya memakai saldo awal bulan yang memuat tanggal mulai, sehingga
  laporan "1–15 Maret" memakai saldo awal Maret tanpa menghitung transaksi
  sebelum tanggal mulai. Baris "Saldo akhir" karena itu tidak sama dengan saldo
  kas sebenarnya, dan selisihnya diam — tidak ada yang tampak salah.
- **Transaksi tidak lagi dapat digeser keluar dari periode yang sudah ditutup.**
  Pemeriksaan kini menyertakan tanggal lama, bukan hanya tanggal baru.
- **Berkas unggahan lama dihapus** saat kolomnya diganti atau barisnya dihapus.
  Filament hanya menulis path ke kolom dan tidak pernah membersihkan berkasnya.
- **Baris panjang pada berkas `.ics` dilipat** sesuai RFC 5545 §3.1 dan dihitung
  dalam oktet, bukan karakter. Pengurai yang taat sebelumnya menolak berkasnya.
- **Wildcard `%` dan `_` pada kotak pencarian di-escape.** Mencari "diskon 50%"
  sebelumnya berarti mencari "diskon 50" diikuti apa saja.
- **Verifikasi dua langkah dibatalkan pada setiap peristiwa Login**, bukan hanya
  saat logout — mengikat verifikasi ke sesi masuk yang bersangkutan alih-alih ke
  cara seseorang keluar.
- **Nomor halaman dan filter dibatasi ke daftar yang memang ditawarkan**,
  sehingga `?page=` dan `?bulan=` acak tidak dapat menggelembungkan tabel cache.
- Hasil pencarian tidak pernah masuk cache; tiap kata baru sebelumnya menulis
  satu baris cache yang hidup enam jam.
- Situs tampil benar di belakang proxy TLS — sebelumnya URL aset tetap berskema
  http di halaman https dan diblokir browser sebagai mixed content, membuat
  situs tampil tanpa CSS sama sekali.
