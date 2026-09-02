<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dua Faktor - {{ config('gereja.nama') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4 font-sans">
    <main class="w-full max-w-md bg-white rounded-2xl shadow-brand border border-slate-200/80 p-6 sm:p-8">
        <div class="w-12 h-12 rounded-xl bg-hkbp-900 text-gold-400 flex items-center justify-center mb-5">
            <span class="text-xl font-black" aria-hidden="true">2FA</span>
        </div>
        <h1 class="text-2xl font-black text-hkbp-950">Verifikasi keamanan</h1>
        <p class="mt-2 text-sm text-slate-600">Masukkan kode enam digit dari aplikasi autentikator atau satu kode pemulihan.</p>

        <form action="{{ route('two-factor.verify') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="kode" class="block text-sm font-bold text-hkbp-900 mb-1">Kode verifikasi</label>
                <input id="kode" name="kode" value="{{ old('kode') }}" inputmode="numeric" autocomplete="one-time-code" autofocus required maxlength="20"
                       class="w-full min-h-12 rounded-xl border {{ $errors->has('kode') ? 'border-red-400' : 'border-slate-300' }} px-4 text-lg tracking-widest font-bold focus:outline-none focus:ring-2 focus:ring-hkbp-800/30">
                @error('kode')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <button class="w-full min-h-12 rounded-xl bg-hkbp-800 hover:bg-hkbp-900 text-white font-bold">Verifikasi dan lanjutkan</button>
        </form>
    </main>
</body>
</html>
