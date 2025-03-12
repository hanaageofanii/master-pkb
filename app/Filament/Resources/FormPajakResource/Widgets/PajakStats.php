<?php

namespace App\Filament\Resources\FormPajakResource\Widgets;

use App\Models\form_pajak;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Filament\Widgets\StatsOverviewWidget\Card;

class PajakStats extends BaseWidget
{

    
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '10s';
    protected static bool $isLazy = false;
    protected ?string $heading = 'Dashboard Validasi';
    protected function getStats(): array
    {
        return [
            Card::make('Total Data Sertifikat', form_pajak::count())
            ->extraAttributes([
                'style' => 'background-color: #FFC107; border-color: #234C63;'
            ]), 
            Card::make('Total Site Plan', form_pajak::distinct('siteplan')->count('siteplan'))
            ->extraAttributes([
                'style' => 'background-color: #FFC107; border-color: #234C63;'
            ]),  
            Card::make('Jumlah Validasi Unit Standar', form_pajak::where('kavling', 'standar')->count())
            ->extraAttributes([
                'style' => 'background-color:#FFC107; form_pajak; border-color: #234C63;'
            ]),            
            Card::make('Jumlah Validasi Unit Khusus', form_pajak::where('kavling', 'khusus')->count())
            ->extraAttributes([
                'style' => 'background-color:#FFC107; form_pajak; border-color: #234C63;'
            ]),
            Card::make('Jumlah Validasi Unit Hook', form_pajak::where('kavling', 'hook')->count())
            ->extraAttributes([
                'style' => 'background-color: #FFC107;form_pajak; border-color: #234C63;'
            ]),
            Card::make('Jumlah Validasi Unit Komersil', form_pajak::where('kavling', 'komersil')->count())
            ->extraAttributes([
                'style' => 'background-color:#FFC107; form_pajak; border-color: #234C63;'
            ]),
            Card::make('Jumlah Validasi Unit Tanah Lebih', form_pajak::where('kavling', 'tanah_lebih')->count())
            ->extraAttributes([
                'style' => 'background-color:#FFC107; form_pajak; border-color: #234C63;'
            ]),
            Card::make('Jumlah Validasi Unit Kios', form_pajak::where('kavling', 'kios')->count())
            ->extraAttributes([
                'style' => 'background-color:#FFC107; form_pajak; border-color: #234C63;'
            ]),
        ];
    }
}
