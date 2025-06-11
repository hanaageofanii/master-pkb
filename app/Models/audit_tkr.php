<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class audit_tkr extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    protected $fillable = [
        "siteplan",
        "type",
        "terbangun",
        "status",
        "kode1",
        "kode2",
        "kode3",
        "kode4",
        "luas1",
        "luas2",
        "luas3",
        "luas4",
        "tanda_terima_sertifikat",
        "nop_pbb_pecahan",
        "tanda_terima_nop",
        "imb_pbg",
        "tanda_terima_imb_pbg",
        "tanda_terima_tambahan",
        "up_sertifikat", "up_nop", "up_imb_pbg", "up_tambahan_lainnya"


    ];

    protected $casts = [
        "up_sertifikat" => 'array',
        "up_nop" => 'array',
        "up_imb_pbg" => 'array',
        "up_tambahan_lainnya" => 'array',
    ];
}
