<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\Renungan;
use App\Models\WartaJemaat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $parhalados = Parhalado::all();

        $jadwal_ibadah = JadwalIbadah::orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->get();

        $warta = WartaJemaat::orderBy('tanggal', 'desc')->take(3)->get();

        // Satu query dengan conditional aggregation, menggantikan 2 query terpisah
        // (Pemasukan & Pengeluaran) yang sebelumnya dijalankan berurutan.
        $kasTotals = KasGereja::selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as total_pemasukan,
                COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as total_pengeluaran
            ")
            ->first();

        $total_pemasukan = (int) $kasTotals->total_pemasukan;
        $total_pengeluaran = (int) $kasTotals->total_pengeluaran;
        $saldo_akhir = $total_pemasukan - $total_pengeluaran;

        $renungans = Renungan::orderBy('tanggal', 'desc')->take(3)->get();
        $galeris = Galeri::orderBy('tanggal', 'desc')->take(6)->get();

        return view('welcome', compact(
            'parhalados',
            'jadwal_ibadah',
            'warta',
            'total_pemasukan',
            'total_pengeluaran',
            'saldo_akhir',
            'renungans',
            'galeris'
        ));
    }

    public function pelayan(): View
    {
        $pendeta = Parhalado::where('kategori', 'Pendeta')->get();
        $parhalado = Parhalado::where('kategori', 'Parhalado')->get()->groupBy('bidang');
        $kategorial = Parhalado::where('kategori', 'Kategorial')->get()->groupBy('bidang');

        return view('pelayan', compact('pendeta', 'parhalado', 'kategorial'));
    }

    public function profil(): View
    {
        return view('profil');
    }

    public function renungan(Request $request): View
    {
        $selected_date = $request->query('tanggal', now()->format('Y-m-d'));

        $renungan = Renungan::whereDate('tanggal', $selected_date)->first();
        $recent_renungans = Renungan::orderBy('tanggal', 'desc')->take(5)->get();

        return view('renungan', compact('renungan', 'selected_date', 'recent_renungans'));
    }

    public function galeri(): View
    {
        $galeris = Galeri::orderBy('tanggal', 'desc')->paginate(12);

        return view('galeri', compact('galeris'));
    }
}