@echo off
REM ---------------------------------------------------------------------------
REM Membuka halaman admin di browser.
REM
REM Memakai alamat publik yang sedang aktif bila presentasi.cmd sedang berjalan,
REM dan jatuh ke http://127.0.0.1:8000/admin bila tidak. Server lokal dinyalakan
REM sendiri bila perlu.
REM ---------------------------------------------------------------------------

title Buka Admin HKBP Volker

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0skrip\buka-admin.ps1"

timeout /t 3 >nul
