# Konvensi Penamaan & Susunan Berkas

Berkas ini menjawab **"kalau saya menambah sesuatu, namanya harus bagaimana"**.

`README.md` menjelaskan cara menjalankan dan mengoperasikan situs, `PLAN.md`
menjelaskan arah, `CHANGELOG.md` mencatat riwayat. Yang di sini tidak
mengulang ketiganya.

Aturan di bawah bukan hasil rancangan di depan; sebagian besarnya sudah diikuti
kode sejak awal. Yang dilakukan pada 4 September 2026 hanyalah menuliskannya dan
membereskan yang menyimpang — karena konvensi yang tidak tertulis hanya bertahan
selama orang yang mengingatnya masih ada.

---

## 1. Bahasa: Inggris untuk kerangka, Indonesia untuk isi gereja

| Kelompok | Bahasa | Contoh |
|---|---|---|
| Istilah framework & teknis | Inggris | `Controller`, `Policy`, `Resource`, `SecurityHeaders`, `Totp` |
| Konsep gereja | Indonesia | `JadwalIbadah`, `Parhalado`, `WartaJemaat`, `PermohonanDoa` |
| Perilaku yang ditulis sendiri | Indonesia | `MembersihkanBerkas`, `CacheKonten`, `PengoptimalGambar` |

Yang tidak boleh adalah **mencampur tata bahasanya di dalam satu kata** —
lihat aturan berikutnya.

## 2. Jangan menjamakkan kata Indonesia dengan `-s`

Bahasa Indonesia tidak menjamakkan dengan akhiran. `galeris`, `parhalados`,
dan `kas_gerejas` bukan Inggris dan bukan Indonesia.

Ini bukan soal keindahan. Generator Laravel dan Filament menerapkan penjamakan
Inggris secara otomatis, sementara berkas yang ditulis tangan tidak — sehingga
dua konvensi hidup berdampingan dan setiap orang harus mengingat mana ikut yang
mana. Yang dipilih: **tidak menjamakkan sama sekali.**

**Tabel** memakai bentuk tunggal, dan modelnya menyebut namanya eksplisit
karena tebakan Laravel salah:

```php
protected $table = 'kas_gereja';
```

**Halaman Filament** memakai pola `<Aksi><NamaModel>`, tanpa penjamakan —
termasuk untuk model berbahasa Inggris, supaya aturannya tidak punya
pengecualian:

```
ListKasGereja   CreateKasGereja   EditKasGereja
ListUser        CreateUser        EditUser
```

**Pengecualian yang disengaja:** tabel milik framework (`users`, `cache`,
`jobs`, `sessions`, `notifications`) dibiarkan apa adanya. Namanya bahasa
Inggris, penjamakannya benar, dan Laravel sendiri yang menentukannya.

**Yang belum ikut:** nama variabel yang diteruskan ke Blade masih membawa
bentuk `$galeris`, `$renungans`, `$parhalados`. Itu diketahui dan sengaja
dibiarkan — mengubahnya menyentuh belasan berkas Blade tanpa ada yang lebih
mudah dibaca sesudahnya.

## 3. Berkas dan rute

| Hal | Aturan | Contoh |
|---|---|---|
| View | kebab-case Indonesia, sama dengan nama rutenya | `penggunaan-gereja-lacak.blade.php` |
| Rute | bernama, kebab-case Indonesia | `Route::get('/renungan/arsip', ...)->name('renungan.arsip')` |
| Perintah artisan | berawalan `hkbp:`, kata kerja Indonesia | `hkbp:cadangkan`, `hkbp:optimalkan-gambar` |
| Migrasi | kata kerja Indonesia yang menjelaskan akibatnya | `samakan_ejaan_nama_tabel` |
| Test | `<Area>Test`, tanpa keterangan kabur | `LaporanKasTest`, bukan `LaporanKasLanjutanTest` |

Nama test tidak boleh memakai kata yang tidak menerangkan apa pun. "Lanjutan"
hanya berarti "ditulis belakangan", dan itu bukan urusan pembaca. Demikian juga
"Asap" sebagai terjemahan *smoke test* — idiomnya tidak ikut terbawa ke bahasa
Indonesia, jadi yang dipakai adalah apa yang benar-benar diperiksa
(`RenderHalamanPanelTest`).

## 4. Di mana berkas diletakkan

Akar `hkbp-volker/` hanya memuat **yang diklik pengurus** dan berkas konfigurasi
yang memang dicari di sana oleh alatnya (`composer.json`, `phpunit.xml`, dan
seterusnya).

```
presentasi.cmd  buka-admin.cmd  pasang-jadwal.cmd  lepas-jadwal.cmd   <- diklik
skrip/                                                                <- mesinnya
```

Skrip yang hanya dipanggil skrip lain — `.ps1`, `.vbs`, `jadwal-laravel.cmd` —
tinggal di `skrip/`. Sebelumnya kesembilannya berdesakan di akar, dan tidak ada
yang bisa membedakan mana yang boleh diklik.

> **Awas:** tugas terjadwal Windows menyimpan **jalur absolut** ke
> `skrip/jadwal-diam.vbs`. Memindahkan atau mengganti namanya membuat cadangan
> harian berhenti tanpa pesan galat apa pun. Sesudah memindahkannya, jalankan
> `lepas-jadwal.cmd` lalu `pasang-jadwal.cmd`.

## 5. Yang sengaja tidak diseragamkan

Menyeragamkan sesuatu yang dilihat orang luar bukan pekerjaan penamaan, dan
harganya ditanggung orang lain:

- **URL dan nama menu panel** — sudah dibagikan dan dihafal jemaat maupun
  pengurus. Mengganti `/penggunaan-gereja` merusak tautan yang beredar.
- **Nama perintah `hkbp:*`** — tertulis di README, di catatan pengurus, dan di
  entri cron di server.
- **Nama indeks dan kunci asing** — ikut membawa nama tabel lama setelah rename.
  Tidak pernah disebut kode mana pun, dan menggantinya jauh lebih berisiko
  daripada manfaatnya.
