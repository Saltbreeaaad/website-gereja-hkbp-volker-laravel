<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WartaJemaat extends Model
{
    protected $fillable = ['judul', 'file_warta'];

    protected $casts = [
    'tanggal' => 'date',
];
}