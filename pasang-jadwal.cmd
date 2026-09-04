@echo off
REM ---------------------------------------------------------------------------
REM Memasang tugas terjadwal Windows yang menjalankan penjadwal Laravel.
REM
REM Klik dua kali berkas ini sekali saja. Setelah itu cadangan basis data
REM berjalan sendiri (lihat routes/console.php).
REM
REM Tugasnya berjalan TERSEMBUNYI dan hanya saat Anda login — tidak ada kata
REM sandi yang perlu disimpan, dan tidak ada jendela hitam yang berkedip.
REM
REM Melepasnya kembali: lepas-jadwal.cmd
REM ---------------------------------------------------------------------------

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0skrip\jadwal-tugas.ps1" -Aksi pasang

echo.
pause
