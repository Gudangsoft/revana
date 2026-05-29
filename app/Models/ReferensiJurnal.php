<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiJurnal extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jurnal',
        'jenis_jurnal',
        'bidang_ilmu',
        'tahun',
        'referensi',
        'kutipan',
        'format_sitasi',
    ];

    protected $casts = [
        'format_sitasi' => 'array',
    ];

    public const STYLE_LABELS = [
        'APA'       => 'APA',
        'IEEE'      => 'IEEE',
        'Harvard'   => 'Harvard',
        'Chicago'   => 'Chicago',
        'Vancouver' => 'Vancouver',
        'MLA'       => 'MLA',
        'ABNT'      => 'ABNT',
        'ACS'       => 'ACS',
        'ACM'       => 'ACM',
    ];
}
