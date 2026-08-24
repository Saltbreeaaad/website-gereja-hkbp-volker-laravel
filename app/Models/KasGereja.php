<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasGereja extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis',
        'keterangan',
        'nominal',
    ];

    protected $casts = [
    'tanggal' => 'date',
];
}