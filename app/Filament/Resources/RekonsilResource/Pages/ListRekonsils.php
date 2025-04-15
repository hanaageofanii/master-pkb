<?php

namespace App\Filament\Resources\RekonsilResource\Pages;

use App\Filament\Resources\RekonsilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekonsils extends ListRecords
{
    protected static string $resource = RekonsilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat Data Transaksi Internal'),
        ];
    }
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         pencairanDajamStats::class,
    //     ];
    // }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
        ->label('Simpan');
    }
}
