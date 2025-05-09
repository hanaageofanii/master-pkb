<?php

namespace App\Filament\Resources\BukuRekonsilResource\Pages;

use App\Filament\Resources\BukuRekonsilResource;
use App\Filament\Resources\BukuRekonsilResource\Widgets\BukuRekonsilStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBukuRekonsils extends ListRecords
{
    protected static string $resource = BukuRekonsilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Buat Data Rekonsil'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BukuRekonsilStats::class,
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
        ->label('Simpan');
    }
}
