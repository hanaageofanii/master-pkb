<?php

namespace App\Filament\Resources\BukuRekonsilResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\BukuRekonsil;

class BukuRekonsilStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Ambil semua data per perusahaan
        $data = BukuRekonsil::select(
                'nama_perusahaan',
                DB::raw("SUM(CASE WHEN tipe = 'debit' THEN jumlah_uang ELSE 0 END) AS total_debit"),
                DB::raw("SUM(CASE WHEN tipe = 'kredit' THEN jumlah_uang ELSE 0 END) AS total_kredit"),
                DB::raw("SUM(CASE WHEN tipe = 'debit' THEN jumlah_uang ELSE -jumlah_uang END) AS saldo")
            )
            ->groupBy('nama_perusahaan')
            ->get();

        $stats = [];

        foreach ($data as $row) {
            $perusahaan = ucwords(str_replace('_', ' ', $row->nama_perusahaan));

            $stats[] = Stat::make("{$perusahaan} - Debit", number_format($row->total_debit, 0, ',', '.'))
                ->description('Total Debit')
                ->color('success');

            $stats[] = Stat::make("{$perusahaan} - Kredit", number_format($row->total_kredit, 0, ',', '.'))
                ->description('Total Kredit')
                ->color('danger');

            $stats[] = Stat::make("{$perusahaan} - Saldo", number_format($row->saldo, 0, ',', '.'))
                ->description('Saldo Akhir')
                ->color('primary');
        }

        return $stats;
    }
}
