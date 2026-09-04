' ---------------------------------------------------------------------------
' Peluncur senyap untuk jadwal-laravel.cmd.
'
' Windows Task Scheduler tidak punya opsi "sembunyikan jendela" untuk skrip
' batch. Menjalankannya langsung membuat jendela konsol berkedip SETIAP MENIT
' di layar — cukup untuk membuat orang mematikan tugasnya dalam sehari.
'
' WScript.Shell.Run dengan gaya jendela 0 menjalankannya benar-benar tanpa
' jendela. Argumen ketiga False berarti tidak menunggu selesai, sehingga tugas
' terjadwalnya langsung berakhir dan tidak pernah menumpuk.
' ---------------------------------------------------------------------------

Dim shell, skrip
Set shell = CreateObject("WScript.Shell")
skrip = Left(WScript.ScriptFullName, InStrRev(WScript.ScriptFullName, "\")) & "jadwal-laravel.cmd"

shell.Run """" & skrip & """", 0, False
