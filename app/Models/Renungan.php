<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Renungan extends Model
{
    protected $fillable = ['judul', 'penulis', 'foto', 'isi'];

    protected $casts = [
        'tanggal' => 'date',
    ];
}