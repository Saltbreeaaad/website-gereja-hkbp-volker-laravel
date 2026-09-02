<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas {{ $dari->translatedFormat('d M Y') }}–{{ $sampai->translatedFormat('d M Y') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 font-sans">
    <main class="max-w-5xl mx-auto my-8 bg-white rounded-2xl shadow-brand p-6 sm:p-10 print-area">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 mb-8">
            <div>
                <p class="text-sm font-bold text-hkbp-700 uppercase tracking-wide">{{ config('gereja.nama') }}</p>
                <h1 class="text-3xl font-black text-hkbp-950 mt-1">Laporan Kas Gereja</h1>
                <p class="text-slate-500 mt-1">{{ $dari->translatedFormat('d F Y') }} – {{ $sampai->translatedFormat('d F Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2" data-no-print>
                <button type="button" data-print class="min-h-11 px-4 rounded-xl bg-hkbp-800 text-white font-bold text-sm">Cetak / Simpan PDF</button>
                <a href="{{ route('admin.kas.csv', ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()]) }}" class="inline-flex items-center min-h-11 px-4 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm">Unduh CSV</a>
            </div>
        </div>

        <form method="GET" class="grid sm:grid-cols-[1fr_1fr_auto] gap-3 mb-8 p-4 bg-slate-50 rounded-xl" data-no-print>
            <label class="text-sm font-bold">Dari<input type="date" name="dari" value="{{ $dari->toDateString() }}" class="block w-full mt-1 min-h-11 rounded-lg border border-slate-300 px-3"></label>
            <label class="text-sm font-bold">Sampai<input type="date" name="sampai" value="{{ $sampai->toDateString() }}" class="block w-full mt-1 min-h-11 rounded-lg border border-slate-300 px-3"></label>
            <button class="self-end min-h-11 px-5 rounded-lg bg-hkbp-800 text-white font-bold text-sm">Terapkan</button>
        </form>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
            @foreach([
                ['Saldo Awal', $saldoAwal, 'text-slate-800'],
                ['Pemasukan', $pemasukan, 'text-emerald-700'],
                ['Pengeluaran', $pengeluaran, 'text-red-700'],
                ['Saldo Akhir', $saldoAkhir, 'text-hkbp-900'],
            ] as [$label, $nilai, $warna])
                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500 uppercase">{{ $label }}</p>
                    <p class="mt-1 font-black {{ $warna }}">Rp {{ number_format($nilai, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead><tr class="bg-hkbp-950 text-white"><th class="text-left p-3">Tanggal</th><th class="text-left p-3">Jenis</th><th class="text-left p-3">Keterangan</th><th class="text-right p-3">Nominal</th></tr></thead>
                <tbody>
                    @forelse($transaksi as $item)
                        <tr class="border-b border-slate-200"><td class="p-3 whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td><td class="p-3">{{ $item->jenis }}</td><td class="p-3">{{ $item->keterangan }}</td><td class="p-3 text-right whitespace-nowrap">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-500">Tidak ada transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-8 text-xs text-slate-500">Dibuat {{ now()->translatedFormat('d F Y, H:i') }} WIB.</p>
    </main>
</body>
</html>
