@echo off
REM ---------------------------------------------------------------------------
REM Menyalakan website dan membuatnya bisa dibuka orang lain lewat internet.
REM
REM Klik dua kali berkas ini sebelum presentasi. Tiga hal dijalankan:
REM   1. MySQL          (kalau belum menyala)
REM   2. php artisan serve pada port 8000
REM   3. Cloudflare Tunnel  -> menghasilkan URL https publik
REM
REM URL-nya dicetak di jendela ini. Bagikan URL itu ke siapa pun.
REM
REM PENTING:
REM   * Jendela ini harus TETAP TERBUKA selama presentasi. Menutupnya
REM     mematikan tunnel dan URL-nya langsung tidak bisa dibuka.
REM   * URL berganti setiap kali dijalankan ulang. Jangan dicetak di undangan.
REM   * Laptop harus tetap menyala dan terhubung internet.
REM ---------------------------------------------------------------------------

setlocal
title Presentasi Website HKBP Volker

set "PROYEK=%~dp0."
REM start-mysql.cmd tinggal di folder induk, di luar repo.
set "MYSQL_CMD=%~dp0..\start-mysql.cmd"
set "PHP=%USERPROFILE%\scoop\shims\php.exe"
set "TUNNEL=%USERPROFILE%\scoop\shims\cloudflared.exe"

if not exist "%PHP%" set "PHP=php"

if not exist "%TUNNEL%" (
    echo [GAGAL] cloudflared belum terpasang. Jalankan:
    echo         scoop install cloudflared
    pause
    exit /b 1
)

echo.
echo  [1/3] Menyalakan MySQL...
if exist "%MYSQL_CMD%" call "%MYSQL_CMD%" >nul 2>&1

powershell -NoProfile -Command "$c = New-Object Net.Sockets.TcpClient; try { $c.Connect('127.0.0.1', 3306); exit 0 } catch { exit 1 } finally { $c.Dispose() }"
if errorlevel 1 (
    echo        [GAGAL] MySQL tidak menyala. Jalankan start-mysql.cmd dulu.
    pause
    exit /b 1
)
echo        MySQL siap.

cd /d "%PROYEK%" || exit /b 1

echo.
echo  [2/3] Membangun aset dan menyalakan server...

REM Aset harus dibangun ulang: berkas di public/build dinamai menurut isi,
REM dan halaman menunjuk nama hasil build terakhir.
call npm run build >nul 2>&1

start "Server Laravel HKBP" /min cmd /c ""%PHP%" artisan serve --port=8000"

powershell -NoProfile -Command "foreach ($i in 1..30) { try { Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 2 | Out-Null; exit 0 } catch { Start-Sleep -Milliseconds 500 } }; exit 1"
if errorlevel 1 (
    echo        [GAGAL] Server tidak merespons.
    pause
    exit /b 1
)
echo        Server siap di http://127.0.0.1:8000

echo.
echo  [3/3] Membuka tunnel ke internet...
echo.
echo  ============================================================
echo   Tunggu baris berisi  https://....trycloudflare.com
echo   Itulah alamat yang Anda bagikan.
echo.
echo   Alamatnya butuh ~15 detik sebelum benar-benar bisa dibuka.
echo   BIARKAN JENDELA INI TERBUKA selama presentasi.
echo  ============================================================
echo.

"%TUNNEL%" tunnel --url http://127.0.0.1:8000

echo.
echo  Tunnel berhenti. Website tidak lagi bisa dibuka dari luar.
pause
