@echo off
REM ---------------------------------------------------------------------------
REM Menyalakan SELURUH website dan membuatnya bisa dibuka orang lain.
REM
REM Klik dua kali berkas ini sebelum presentasi. Empat hal dijalankan berurutan:
REM   1. MySQL
REM   2. Build aset (public/build)
REM   3. Server Laravel di port 8000
REM   4. Cloudflare Tunnel -> alamat https publik
REM
REM Setelah siap, browser dibuka otomatis ke halaman depan DAN halaman admin.
REM
REM PENTING: jendela ini harus TETAP TERBUKA selama presentasi.
REM          Menutupnya mematikan server dan tunnel.
REM ---------------------------------------------------------------------------

title Presentasi Website HKBP Volker

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0skrip\presentasi.ps1"

echo.
pause
