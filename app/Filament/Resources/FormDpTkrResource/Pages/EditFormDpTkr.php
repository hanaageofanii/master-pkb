<?php

namespace App\Filament\Resources\FormDpTkrResource\Pages;

use App\Filament\Resources\FormDpTkrResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormDpTkr extends EditRecord
{
    protected static string $resource = FormDpTkrResource::class;

    protected static ?string $title = "Ubah Data Form DP";

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->label('Hapus Data Uang Muka')
            ->modalHeading(fn ($record) => "Konfirmasi Hapus {$record->siteplan}")
            ->modalDescription(fn ($record) => "Apakah Anda yakin ingin menghapus blok {$record->siteplan}?"),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
        ->label('Simpan');
    }
}
