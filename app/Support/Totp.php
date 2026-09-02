<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class Totp
{
    private const ALFABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function buatRahasia(int $panjangBita = 20): string
    {
        return self::base32Encode(random_bytes($panjangBita));
    }

    public static function verifikasi(string $rahasia, string $kode, int $jendela = 1): bool
    {
        return self::langkahCocok($rahasia, $kode, $jendela) !== null;
    }

    /**
     * Verifikasi kode dan langsung membakarnya: kode yang sama tidak akan
     * pernah diterima dua kali.
     *
     * RFC 6238 §5.2 mensyaratkan ini, dan tanpanya satu kode tetap sah selama
     * ~90 detik (tiga langkah waktu, karena jendela toleransinya ±1). Kode yang
     * terbaca dari balik bahu, tertinggal di layar yang tidak terkunci, atau
     * terkirim ke halaman phishing masih dapat dipakai ulang oleh orang lain
     * selama sisa jendela itu. Pembatasan laju di route hanya memperlambat
     * penebakan; ia tidak menghalangi pemakaian ulang kode yang memang benar.
     *
     * `Cache::add()` bersifat atomik — ia menulis hanya bila kuncinya belum ada
     * — sehingga dua permintaan yang datang bersamaan dengan kode yang sama
     * tidak dapat sama-sama lolos.
     *
     * $penanda memisahkan penghitungan per akun; isi dengan id pengguna. Tanpa
     * itu, kode yang dipakai satu pengurus akan memblokir pengurus lain yang
     * kebetulan menghasilkan enam angka yang sama.
     */
    public static function verifikasiSekali(string $rahasia, string $kode, string|int $penanda, int $jendela = 1): bool
    {
        $langkah = self::langkahCocok($rahasia, $kode, $jendela);

        if ($langkah === null) {
            return false;
        }

        // 120 detik: lebih panjang dari jendela terlebar yang mungkin diterima
        // (tiga langkah = 90 detik), jadi catatannya tidak pernah kedaluwarsa
        // lebih dulu daripada kodenya sendiri.
        return Cache::add("totp-terpakai:{$penanda}:{$langkah}", true, 120);
    }

    /**
     * Langkah waktu yang cocok dengan kode, atau null bila tidak ada.
     *
     * Mengembalikan langkahnya — bukan sekadar true — supaya pemanggil dapat
     * mencatat kode mana yang sudah terpakai; lihat verifikasiSekali().
     */
    public static function langkahCocok(string $rahasia, string $kode, int $jendela = 1): ?int
    {
        $kode = preg_replace('/\D/', '', $kode);

        if (! is_string($kode) || strlen($kode) !== 6) {
            return null;
        }

        // Rahasia kosong atau bukan base32 tetap menghasilkan kunci HMAC yang
        // sah secara teknis (string kosong), sehingga kode dapat dihitung siapa
        // pun tanpa perlu mengetahui rahasia apa pun. Akun tanpa rahasia harus
        // menolak, bukan menerima kode yang bisa ditebak dari waktu saja.
        if (self::base32Decode($rahasia) === '') {
            return null;
        }

        $langkah = intdiv(time(), 30);

        for ($selisih = -$jendela; $selisih <= $jendela; $selisih++) {
            if (hash_equals(self::kode($rahasia, $langkah + $selisih), $kode)) {
                return $langkah + $selisih;
            }
        }

        return null;
    }

    public static function uri(string $rahasia, string $email): string
    {
        $penerbit = config('gereja.nama_pendek');
        $label = rawurlencode($penerbit.':'.$email);

        return "otpauth://totp/{$label}?secret={$rahasia}&issuer=".rawurlencode($penerbit).'&digits=6&period=30';
    }

    private static function kode(string $rahasia, int $langkah): string
    {
        $kunci = self::base32Decode($rahasia);
        $pesan = pack('N2', intdiv($langkah, 0x100000000), $langkah & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $pesan, $kunci, true);
        $offset = ord($hash[19]) & 0x0F;
        $angka = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($angka % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $bita = '';
        foreach (str_split($data) as $karakter) {
            $bita .= str_pad(decbin(ord($karakter)), 8, '0', STR_PAD_LEFT);
        }

        $hasil = '';
        foreach (str_split($bita, 5) as $bagian) {
            $hasil .= self::ALFABET[bindec(str_pad($bagian, 5, '0'))];
        }

        return $hasil;
    }

    private static function base32Decode(string $data): string
    {
        $bita = '';
        foreach (str_split(strtoupper($data)) as $karakter) {
            $posisi = strpos(self::ALFABET, $karakter);
            if ($posisi !== false) {
                $bita .= str_pad(decbin($posisi), 5, '0', STR_PAD_LEFT);
            }
        }

        $hasil = '';
        foreach (str_split($bita, 8) as $bagian) {
            if (strlen($bagian) === 8) {
                $hasil .= chr(bindec($bagian));
            }
        }

        return $hasil;
    }
}
