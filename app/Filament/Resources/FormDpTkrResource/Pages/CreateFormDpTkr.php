<?php

namespace App\Filament\Resources\FormDpTkrResource\Pages;

use App\Filament\Resources\FormDpTkrResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateFormDpTkr extends CreateRecord
{
    protected static string $resource = FormDpTkrResource::class;
    protected static ?string $title = "Buat Data DP";
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
            ->title('Data Uang Muka Disimpan')
            ->body('Data Uang Muka telah berhasil disimpan.');
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
        ->label('Simpan');
    }
}


