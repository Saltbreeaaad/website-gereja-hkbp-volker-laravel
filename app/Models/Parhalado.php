<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parhalado extends Model
{
    protected $fillable = [
        'nama',
        'foto',
        'kategori',
        'jabatan',
        'bidang', 
        'keterangan',
        'telepon',
    ];
}