<?php

namespace App\Support;

final class Totp
{
    private const ALFABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function buatRahasia(int $panjangBita = 20): string
    {
        return self::base32Encode(random_bytes($panjangBita));
    }

    public static function verifikasi(string $rahasia, string $kode, int $jendela = 1): bool
    {
        $kode = preg_replace('/\D/', '', $kode);

        if (! is_string($kode) || strlen($kode) !== 6) {
            return false;
        }

        // Rahasia kosong atau bukan base32 tetap menghasilkan kunci HMAC yang
        // sah secara teknis (string kosong), sehingga kode dapat dihitung siapa
        // pun tanpa perlu mengetahui rahasia apa pun. Akun tanpa rahasia harus
        // menolak, bukan menerima kode yang bisa ditebak dari waktu saja.
        if (self::base32Decode($rahasia) === '') {
            return false;
        }

        $langkah = intdiv(time(), 30);

        for ($selisih = -$jendela; $selisih <= $jendela; $selisih++) {
            if (hash_equals(self::kode($rahasia, $langkah + $selisih), $kode)) {
                return true;
            }
        }

        return false;
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
