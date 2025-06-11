<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class ajb extends Model
{
    use HasFactory, SoftDeletes, HasRoles;


    protected $fillable = [
        'siteplan',
        'nop',
        'nama_konsumen',
        'nik',
        'npwp',
        'alamat',
        'suket_validasi',
        'no_sspd_bphtb',
        'tanggal_sspd_bphtb',
        'no_validasi_sspd_bphtb',
        'notaris',
        'no_ajb',
        'tanggal_ajb',
        'no_bast',
        'tanggal_bast',
        'up_bast',
        'up_validasi_bphtb',
    ];

    protected $casts = [
        "up_bast" => 'array',
        "up_validasi_bphtb" => 'array'
    ];
}
