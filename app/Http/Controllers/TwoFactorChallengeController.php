<?php

namespace App\Http\Controllers;

use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function tampil(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        if (! Auth::user()->twoFactorAktif()) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifikasi(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $data = $request->validate(['kode' => ['required', 'string', 'max:20']]);
        $user = Auth::user();
        $kode = strtoupper(trim($data['kode']));
        // Sekali pakai: kode yang sudah dipakai tidak diterima lagi selama sisa
        // jendela waktunya. Lihat Totp::verifikasiSekali().
        $valid = Totp::verifikasiSekali((string) $user->two_factor_secret, $kode, $user->getKey());

        if (! $valid) {
            $pemulihan = collect($user->two_factor_recovery_codes ?? []);
            $index = $pemulihan->search(fn (string $hash): bool => Hash::check($kode, $hash));

            if ($index !== false) {
                $pemulihan->forget($index);
                $user->forceFill(['two_factor_recovery_codes' => $pemulihan->values()->all()])->save();
                $valid = true;
            }
        }

        if (! $valid) {
            return back()->withErrors(['kode' => 'Kode autentikator atau kode pemulihan tidak valid.']);
        }

        $request->session()->regenerate();
        $request->session()->put('two_factor_verified_user_id', $user->getKey());

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
