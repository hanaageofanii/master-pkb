<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class form_pencocokan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'no_transaksi',
        'no_ref_bank',
        'tanggal_transaksi',
        'jumlah',
        'tipe',
        'status',
        'nominal_selisih',
        'analisis_selisih',
        'tindakan',
        'tanggal_validasi',
        'disetujui_oleh',
        'catatan',
        'bukti_bukti'
    ];

    protected $casts = [
        "bukti_bukti" => 'array',
    ];
}
