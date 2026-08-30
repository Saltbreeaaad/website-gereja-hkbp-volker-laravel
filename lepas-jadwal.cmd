@echo off
REM Melepas tugas terjadwal penjadwal Laravel. Tidak menghapus cadangan apa pun.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0jadwal-tugas.ps1" -Aksi lepas

echo.
pause
