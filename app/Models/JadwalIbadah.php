<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalIbadah extends Model
{
    protected $fillable = ['nama_ibadah', 'pelayan_firman', 'keterangan'];

    protected $casts = [
    'tanggal' => 'date',
    'waktu' => 'datetime',
];
}