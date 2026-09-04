<#
    Menyalakan seluruh website dan membuatnya bisa dibuka orang lain.

    Urutannya: MySQL -> aset -> server Laravel -> Cloudflare Tunnel. Setelah
    tunnel memberi alamat publik, browser dibuka otomatis ke halaman depan dan
    ke halaman admin.

    Alamat tunnel disimpan ke storage/app/alamat-publik.txt supaya
    buka-admin.cmd bisa memakainya lagi tanpa menebak.

    Dipanggil oleh presentasi.cmd. Ditulis sebagai .ps1 tersendiri, bukan
    dirangkai ke dalam `powershell -Command` di dalam .cmd: sambungan baris
    caret (^) di cmd pecah pada teks berspasi.
#>

$ErrorActionPreference = 'Stop'

# Berkas ini ada di skrip\, jadi akar proyek satu tingkat di atasnya.
$akar = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$php = Join-Path $env:USERPROFILE 'scoop\shims\php.exe'
$tunnel = Join-Path $env:USERPROFILE 'scoop\shims\cloudflared.exe'
$logTunnel = Join-Path $akar 'storage\logs\tunnel.log'
$berkasAlamat = Join-Path $akar 'storage\app\alamat-publik.txt'
$port = 8000

if (-not (Test-Path $php)) { $php = 'php' }

# Proses yang kita nyalakan sendiri, untuk dimatikan lagi saat keluar.
$prosesServer = $null
$prosesTunnel = $null

function Tulis-Judul($teks) {
    Write-Host ''
    Write-Host "  $teks" -ForegroundColor Cyan
}

function Port-Terbuka($nomor) {
    $klien = New-Object Net.Sockets.TcpClient
    try { $klien.Connect('127.0.0.1', $nomor); return $true }
    catch { return $false }
    finally { $klien.Dispose() }
}

try {
    if (-not (Test-Path $tunnel)) {
        Write-Host '[GAGAL] cloudflared belum terpasang. Jalankan:  scoop install cloudflared' -ForegroundColor Red
        exit 1
    }

    # --- 1. MySQL ---------------------------------------------------------
    Tulis-Judul '[1/4] MySQL'

    if (Port-Terbuka 3306) {
        Write-Host '        sudah menyala.'
    }
    else {
        $skripMysql = Join-Path (Split-Path -Parent $akar) 'start-mysql.cmd'

        if (Test-Path $skripMysql) {
            & cmd /c "`"$skripMysql`"" | Out-Null
        }

        if (-not (Port-Terbuka 3306)) {
            Write-Host '        [GAGAL] MySQL tidak menyala. Jalankan start-mysql.cmd dulu.' -ForegroundColor Red
            exit 1
        }

        Write-Host '        siap.'
    }

    # --- 2. Aset ----------------------------------------------------------
    # Berkas di public/build dinamai menurut isinya, dan halaman menunjuk nama
    # hasil build terakhir. Tanpa langkah ini, perubahan tampilan tidak muncul.
    Tulis-Judul '[2/4] Membangun aset'
    Push-Location $akar

    # Lewat `cmd /c`, bukan `& npm ... 2>&1`. Di Windows PowerShell 5.1,
    # mengalihkan stderr sebuah program native membungkus tiap barisnya menjadi
    # ErrorRecord — dan dengan $ErrorActionPreference='Stop', catatan biasa yang
    # npm tulis ke stderr ("npm notice run build") menghentikan seluruh skrip
    # sebelum server sempat dinyalakan.
    & cmd /c 'npm run build' | Out-Null
    $kodeBuild = $LASTEXITCODE

    Pop-Location

    if ($kodeBuild -ne 0) {
        Write-Host '        [GAGAL] npm run build gagal. Jalankan sendiri untuk melihat pesannya.' -ForegroundColor Red
        exit 1
    }

    Write-Host '        selesai.'

    # --- 3. Server Laravel ------------------------------------------------
    Tulis-Judul "[3/4] Server Laravel (port $port)"

    if (Port-Terbuka $port) {
        Write-Host '        sudah ada yang mendengarkan; dipakai apa adanya.'
        Write-Host '        [!] Server ini tidak dinyalakan oleh skrip ini, jadi APP_DEBUG-nya' -ForegroundColor Yellow
        Write-Host '            tidak dapat dipastikan mati. Tutup server itu lalu jalankan' -ForegroundColor Yellow
        Write-Host '            ulang bila alamatnya akan dibagikan ke luar.' -ForegroundColor Yellow
    }
    else {
        # APP_DEBUG dimatikan untuk proses ini.
        #
        # Skrip ini membuka situs ke internet lewat Cloudflare Tunnel, sementara
        # .env pengembangan berisi APP_DEBUG=true. Satu galat saja pada alamat
        # publik itu akan menampilkan halaman Ignition lengkap kepada siapa pun
        # yang memegang tautannya: jejak tumpukan, potongan kode, dan seluruh
        # daftar variabel lingkungan — termasuk DB_PASSWORD dan APP_KEY.
        #
        # Diberikan sebagai variabel lingkungan proses, bukan dengan menyunting
        # .env: Dotenv Laravel bersifat immutable dan tidak menimpa nilai yang
        # sudah ada di lingkungan, jadi yang ini menang tanpa berkasnya berubah
        # dan tanpa ada yang perlu diingat untuk dikembalikan nanti.
        $debugSebelumnya = $env:APP_DEBUG
        $env:APP_DEBUG = 'false'

        $prosesServer = Start-Process -FilePath $php `
            -ArgumentList 'artisan', 'serve', "--port=$port" `
            -WorkingDirectory $akar -WindowStyle Hidden -PassThru

        $env:APP_DEBUG = $debugSebelumnya

        $siap = $false
        foreach ($i in 1..40) {
            if (Port-Terbuka $port) { $siap = $true; break }
            Start-Sleep -Milliseconds 500
        }

        if (-not $siap) {
            Write-Host '        [GAGAL] Server tidak merespons.' -ForegroundColor Red
            exit 1
        }

        Write-Host '        siap.'
    }

    # --- 4. Tunnel --------------------------------------------------------
    Tulis-Judul '[4/4] Membuka tunnel ke internet'

    if (Test-Path $logTunnel) { Remove-Item $logTunnel -Force }

    $prosesTunnel = Start-Process -FilePath $tunnel `
        -ArgumentList 'tunnel', '--url', "http://127.0.0.1:$port" `
        -RedirectStandardError $logTunnel -RedirectStandardOutput "$logTunnel.out" `
        -WindowStyle Hidden -PassThru

    # Alamatnya baru muncul di log setelah tunnel terdaftar.
    $alamat = $null
    foreach ($i in 1..60) {
        Start-Sleep -Milliseconds 500

        foreach ($berkas in @($logTunnel, "$logTunnel.out")) {
            if (-not (Test-Path $berkas)) { continue }

            $cocok = Select-String -Path $berkas -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' `
                -AllMatches -ErrorAction SilentlyContinue | Select-Object -First 1

            if ($cocok) { $alamat = $cocok.Matches[0].Value; break }
        }

        if ($alamat) { break }
    }

    if (-not $alamat) {
        Write-Host '        [GAGAL] Alamat tunnel tidak muncul. Periksa storage\logs\tunnel.log' -ForegroundColor Red
        exit 1
    }

    # -Encoding ascii, bukan utf8: Set-Content di Windows PowerShell 5.1 menulis
    # BOM di awal berkas, dan BOM itu ikut terbaca sebagai bagian URL sehingga
    # buka-admin.ps1 menganggap alamatnya tidak bisa dijangkau. Alamat tunnel
    # hanya berisi huruf, angka, dan tanda hubung, jadi ascii sudah cukup.
    Set-Content -Path $berkasAlamat -Value $alamat -Encoding ascii
    Write-Host '        siap.'

    # Cloudflare butuh beberapa detik menyebarkan alamat barunya, dan sesekali
    # alamatnya tidak pernah terbit sama sekali — cloudflared tetap melaporkan
    # "Registered tunnel connection" padahal namanya tidak ada di DNS. Alamat
    # itu HARUS dibuktikan dulu: mengumumkannya tanpa diuji berarti Anda baru
    # tahu saat sudah berdiri di depan hadirin.
    Tulis-Judul 'Menguji alamat sampai benar-benar bisa dibuka'

    $bisaDibuka = $false
    foreach ($i in 1..40) {
        try {
            Invoke-WebRequest -Uri $alamat -UseBasicParsing -TimeoutSec 8 | Out-Null
            $bisaDibuka = $true
            break
        }
        catch { Start-Sleep -Seconds 3 }
    }

    if (-not $bisaDibuka) {
        Write-Host ''
        Write-Host '  [GAGAL] Alamat tidak bisa dijangkau setelah ~2 menit.' -ForegroundColor Red
        Write-Host "          $alamat" -ForegroundColor Red
        Write-Host ''
        Write-Host '  Cloudflare kadang gagal menerbitkan alamat tunnel gratis.' -ForegroundColor Yellow
        Write-Host '  Tutup jendela ini, lalu jalankan presentasi.cmd sekali lagi -'
        Write-Host '  percobaan berikutnya mendapat alamat baru dan biasanya berhasil.'
        Write-Host ''
        Write-Host '  Sementara itu situsnya tetap bisa dibuka di komputer ini:'
        Write-Host "  http://127.0.0.1:$port" -ForegroundColor White
        Write-Host ''
        exit 1
    }

    Write-Host '        alamat terbukti hidup.'

    Write-Host ''
    Write-Host '  ============================================================' -ForegroundColor Green
    Write-Host ''
    Write-Host '   ALAMAT UNTUK DIBAGIKAN:' -ForegroundColor Green
    Write-Host "   $alamat" -ForegroundColor White
    Write-Host ''
    Write-Host '   Halaman admin:' -ForegroundColor Green
    Write-Host "   $alamat/admin" -ForegroundColor White
    Write-Host ''
    Write-Host '  ============================================================' -ForegroundColor Green

    Start-Process $alamat
    Start-Sleep -Seconds 2
    Start-Process "$alamat/admin"

    Write-Host ''
    Write-Host '  Browser dibuka: halaman depan dan halaman admin.'
    Write-Host ''
    Write-Host '  JANGAN TUTUP JENDELA INI selama presentasi.' -ForegroundColor Yellow
    Write-Host '  Tekan Ctrl+C bila sudah selesai.'
    Write-Host ''

    # Menahan skrip tetap hidup. Begitu jendela ditutup atau Ctrl+C ditekan,
    # blok finally di bawah mematikan server dan tunnel yang kita nyalakan.
    while ($true) {
        Start-Sleep -Seconds 5

        if ($prosesTunnel.HasExited) {
            Write-Host '  [!] Tunnel berhenti sendiri. Alamat tidak bisa dibuka lagi.' -ForegroundColor Red
            break
        }
    }
}
finally {
    Write-Host ''
    Write-Host '  Membersihkan...'

    foreach ($p in @($prosesTunnel, $prosesServer)) {
        if ($p -and -not $p.HasExited) {
            Stop-Process -Id $p.Id -Force -ErrorAction SilentlyContinue
        }
    }

    # Alamat lama dihapus supaya buka-admin.cmd tidak menawarkan alamat mati.
    if (Test-Path $berkasAlamat) { Remove-Item $berkasAlamat -Force -ErrorAction SilentlyContinue }

    Write-Host '  Selesai. Website tidak lagi bisa dibuka dari luar.'
    Write-Host ''
}
