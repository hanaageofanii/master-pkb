<?php

namespace App\Filament\Resources\VerifikasiDajamTkrResource\Pages;

use App\Filament\Resources\VerifikasiDajamTkrResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateVerifikasiDajamTkr extends CreateRecord
{
    protected static string $resource = VerifikasiDajamTkrResource::class;
    protected static ?string $title = "Buat Data Verifikasi Dajam";
    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
        ->label('Tambah Data');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
        ->label('Tambah Data Lagi')
        ->color('warning');
    }
    
    protected function getCancelFormAction() : Actions\Action
    {
        return parent::getCancelFormAction()
        ->label('Batal')
        ->color('danger');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Data Verifikasi Dajam Disimpan')
            ->body('Data Verifikasi Dajam telah berhasil disimpan.');
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
        ->label('Simpan');
    }
}


