<#
    Memasang atau melepas tugas terjadwal Windows yang menjalankan penjadwal
    Laravel tiap menit — padanan baris cron di server.

    Dipanggil oleh pasang-jadwal.cmd dan lepas-jadwal.cmd. Ditulis sebagai
    berkas .ps1 tersendiri, bukan dirangkai ke dalam `powershell -Command` di
    dalam .cmd: sambungan baris caret (^) di cmd pecah pada teks berspasi, dan
    jalur proyek maupun deskripsi tugas ini penuh spasi.

    Pemakaian:
        powershell -ExecutionPolicy Bypass -File jadwal-tugas.ps1 -Aksi pasang
        powershell -ExecutionPolicy Bypass -File jadwal-tugas.ps1 -Aksi lepas
#>

param(
    [ValidateSet('pasang', 'lepas')]
    [string]$Aksi = 'pasang'
)

$ErrorActionPreference = 'Stop'

$nama = 'HKBP Volker - Penjadwal Laravel'
$akar = Split-Path -Parent $MyInvocation.MyCommand.Path
$peluncur = Join-Path $akar 'jadwal-diam.vbs'

function Ada-Tugas {
    $null -ne (Get-ScheduledTask -TaskName $nama -ErrorAction SilentlyContinue)
}

if ($Aksi -eq 'lepas') {
    if (-not (Ada-Tugas)) {
        Write-Host "Tugas `"$nama`" memang tidak terpasang."
        exit 0
    }

    Unregister-ScheduledTask -TaskName $nama -Confirm:$false

    Write-Host ''
    Write-Host "[OK] Tugas dilepas. Cadangan yang sudah ada tetap tersimpan di"
    Write-Host "     hkbp-volker\storage\app\backups"
    exit 0
}

if (-not (Test-Path $peluncur)) {
    Write-Host "[GAGAL] jadwal-diam.vbs tidak ditemukan di $akar" -ForegroundColor Red
    exit 1
}

# wscript.exe + peluncur .vbs: satu-satunya cara menjalankan skrip batch tiap
# menit tanpa jendela konsol berkedip di layar.
$aksiTugas = New-ScheduledTaskAction -Execute 'wscript.exe' -Argument ('"{0}"' -f $peluncur)

# -Once + RepetitionInterval 1 menit = berulang tanpa batas, sama seperti
# baris cron `* * * * *`.
$pemicu = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)

$setelan = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -MultipleInstances IgnoreNew `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 30)

Register-ScheduledTask `
    -TaskName $nama `
    -Action $aksiTugas `
    -Trigger $pemicu `
    -Settings $setelan `
    -Description 'Menjalankan penjadwal Laravel tiap menit (padanan cron), untuk cadangan basis data harian HKBP Volker.' `
    -Force | Out-Null

Write-Host ''
Write-Host "[OK] Tugas `"$nama`" terpasang dan berjalan tiap menit."
Write-Host ''
Write-Host '     Cadangan disimpan di : hkbp-volker\storage\app\backups'
Write-Host '     Catatan penjadwal    : hkbp-volker\storage\logs\jadwal.log'
Write-Host '     Catatan cadangan     : hkbp-volker\storage\logs\cadangan.log'
Write-Host ''
Write-Host '     Satu cadangan per hari, dibuat pada menit ke-15 jam pertama'
Write-Host '     MySQL menyala. Kalau MySQL mati, tugasnya berhenti diam-diam.'
