<#
    Membuka halaman admin di browser.

    Mengutamakan alamat publik yang sedang aktif (ditulis presentasi.ps1 ke
    storage/app/alamat-publik.txt), dan jatuh ke alamat lokal bila tunnel-nya
    tidak berjalan. Kalau server lokal pun mati, ia dinyalakan dulu.

    Dipanggil oleh buka-admin.cmd.
#>

$ErrorActionPreference = 'Stop'

# Berkas ini ada di skrip\, jadi akar proyek satu tingkat di atasnya.
$akar = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$php = Join-Path $env:USERPROFILE 'scoop\shims\php.exe'
$berkasAlamat = Join-Path $akar 'storage\app\alamat-publik.txt'
$port = 8000

if (-not (Test-Path $php)) { $php = 'php' }

function Port-Terbuka($nomor) {
    $klien = New-Object Net.Sockets.TcpClient
    try { $klien.Connect('127.0.0.1', $nomor); return $true }
    catch { return $false }
    finally { $klien.Dispose() }
}

# --- 1. Alamat publik yang sedang aktif? ---------------------------------
$alamat = $null

if (Test-Path $berkasAlamat) {
    # Buang BOM bila berkasnya pernah ditulis dengan encoding ber-BOM: karakter
    # itu tidak ikut terbuang Trim() dan merusak URL-nya.
    $tersimpan = (Get-Content $berkasAlamat -Raw).Trim().TrimStart([char]0xFEFF)

    # Berkasnya bisa tertinggal dari sesi yang berhenti paksa, jadi alamatnya
    # dibuktikan dulu — bukan dipercaya begitu saja.
    if ($tersimpan) {
        try {
            Invoke-WebRequest -Uri $tersimpan -UseBasicParsing -TimeoutSec 8 | Out-Null
            $alamat = $tersimpan
            Write-Host "  Memakai alamat publik yang sedang aktif." -ForegroundColor Green
        }
        catch {
            Write-Host '  Alamat publik tersimpan sudah tidak aktif; beralih ke alamat lokal.' -ForegroundColor Yellow
        }
    }
}

# --- 2. Jatuh ke server lokal -------------------------------------------
if (-not $alamat) {
    if (-not (Port-Terbuka 3306)) {
        Write-Host ''
        Write-Host '  [!] MySQL belum menyala. Halaman admin akan gagal memuat data.' -ForegroundColor Yellow
        Write-Host '      Jalankan start-mysql.cmd dulu, atau pakai presentasi.cmd' -ForegroundColor Yellow
        Write-Host '      yang menyalakan semuanya sekaligus.'
        Write-Host ''
    }

    if (-not (Port-Terbuka $port)) {
        Write-Host '  Server belum menyala; menyalakannya...'

        Start-Process -FilePath $php -ArgumentList 'artisan', 'serve', "--port=$port" `
            -WorkingDirectory $akar -WindowStyle Hidden | Out-Null

        $siap = $false
        foreach ($i in 1..30) {
            if (Port-Terbuka $port) { $siap = $true; break }
            Start-Sleep -Milliseconds 500
        }

        if (-not $siap) {
            Write-Host '  [GAGAL] Server tidak merespons.' -ForegroundColor Red
            exit 1
        }
    }

    $alamat = "http://127.0.0.1:$port"
}

$admin = "$alamat/admin"

Write-Host ''
Write-Host "  Membuka: $admin" -ForegroundColor White
Write-Host ''

Start-Process $admin
