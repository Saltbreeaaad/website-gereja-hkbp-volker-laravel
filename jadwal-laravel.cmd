@echo off
REM ---------------------------------------------------------------------------
REM Satu detak untuk penjadwal Laravel — padanan Windows dari baris cron
REM
REM     * * * * * cd /path && php artisan schedule:run
REM
REM Dipanggil tiap menit oleh Windows Task Scheduler (tugas "HKBP Volker -
REM Penjadwal Laravel"). Laravel sendiri yang memutuskan apa yang jatuh tempo;
REM sebagian besar panggilan tidak mengerjakan apa pun dan langsung selesai.
REM
REM Yang dijadwalkan saat ini: cadangan basis data harian
REM (lihat routes/console.php).
REM
REM Memasang / melepas tugasnya:
REM   pasang-jadwal.cmd
REM   lepas-jadwal.cmd
REM ---------------------------------------------------------------------------

setlocal

REM %~dp0 = direktori berkas ini, berakhiran backslash. Dipakai supaya tugas
REM terjadwal tidak bergantung pada direktori kerja yang diberikan Windows.
set "PROYEK=%~dp0."
set "PHP=%USERPROFILE%\scoop\shims\php.exe"

if not exist "%PHP%" set "PHP=php"

cd /d "%PROYEK%" || exit /b 1

REM MySQL mati adalah keadaan normal di mesin pengembangan, bukan kegagalan.
REM Tanpa pemeriksaan ini, tiap menit menghasilkan satu galat koneksi di log.
powershell -NoProfile -Command "$c = New-Object Net.Sockets.TcpClient; try { $c.Connect('127.0.0.1', 3306); exit 0 } catch { exit 1 } finally { $c.Dispose() }"
if errorlevel 1 exit /b 0

"%PHP%" artisan schedule:run >> "%PROYEK%\storage\logs\jadwal.log" 2>&1

exit /b 0
