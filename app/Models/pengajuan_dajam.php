<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class pengajuan_dajam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
    'siteplan',
    'bank',
    'no_debitur',
    'nama_konsumen',
    'nama_dajam',
    'no_surat',
    'tanggal_pengajuan',
    'nilai_pencairan',
    'status_dajam',
    'up_surat_pengajuan',
    'up_nominatif_pengajuan',
    ];
}
