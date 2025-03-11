<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormLegalResource\Pages;
use App\Filament\Resources\FormLegalResource\RelationManagers;
use App\Models\form_legal;
use App\Models\FormLegal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GCVResource;
use App\Models\GCV;
use App\Models\form_kpr;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;



class FormLegalResource extends Resource
{
    protected static ?string $model = form_legal::class;

    protected static ?string $title = "Input Sertifikat";
    protected static ?string $navigationGroup = "Legal";
    protected static ?string $pluralLabel = "Form Input Sertifikat";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Input Sertifikat';
    protected static ?string $pluralModelLabel = 'Daftar Input Sertifikat';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\Select::make('siteplan')
                        ->label('Blok')
                        ->nullable()
                        ->options(fn ($get, $set, $record) => 
                            form_kpr::where('status_akad', 'akad') 
                                ->pluck('siteplan', 'siteplan')
                                ->toArray()
                            + ($record?->siteplan ? [$record->siteplan => $record->siteplan] : [])
                        )
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $gcv = form_kpr::where('siteplan', $state)->first(); 

                            if ($gcv) {
                                $set('nama_konsumen', $gcv->nama_konsumen);
                            }
                        }),

                        Forms\Components\TextInput::make('nama_konsumen')->nullable()->label('Nama Konsumen'),

                        Forms\Components\TextInput::make('id_rumah')->nullable()->label('No. ID Rumah'),

                        Forms\Components\Select::make('status_sertifikat')
                        ->label('Status Sertifikat')
                        ->options([
                            'induk' => 'Induk',
                            'pecahan' => 'Pecahan',
                        ])->nullable(),

                        Forms\Components\TextInput::make('no_sertifikat')->nullable()->label('No. Sertifikat'),
                        Forms\Components\TextInput::make('nib')->nullable()->label('NIB'),

                        Forms\Components\TextInput::make('luas_sertifikat')->nullable()->label('Luas Sertifikat'),
                        Forms\Components\TextInput::make('imb_pbg')->nullable()->label('IMB/PBG'),

                        Forms\Components\TextInput::make('nop')->nullable()->label('NOP'),
                        Forms\Components\TextInput::make('nop1')->nullable()->label('NOP Tambahan'),

                    Forms\Components\Fieldset::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('up_sertifikat')->disk('public')->nullable()->label('Dokumen Sertifikat')->required(false),
                        Forms\Components\FileUpload::make('up_pbb')->disk('public')->nullable()->label('Dokumen PBB')->required(false),
                        Forms\Components\FileUpload::make('up_img')->disk('public')->nullable()->label('Dokumen IMG')->required(false),
                    ]),                        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siteplan')->sortable()->searchable()->label('Blok'),
                Tables\Columns\TextColumn::make('nama_konsumen')->sortable()->searchable()->label('Nama Konsumen'),
                Tables\Columns\TextColumn::make('id_rumah')->sortable()->searchable()->label('No. ID Rumah'),
                Tables\Columns\TextColumn::make('status_sertifikat')->sortable()->searchable()->label('Status Sertifikat'),
                Tables\Columns\TextColumn::make('no_sertifikat')->sortable()->searchable()->label('No. Sertifikat'),
                Tables\Columns\TextColumn::make('luas_sertifikat')->sortable()->searchable()->label('Luas Sertifikat'),
                Tables\Columns\TextColumn::make('nop')->sortable()->searchable()->label('NOP'),
                Tables\Columns\TextColumn::make('nop1')->sortable()->searchable()->label('NOP Tambahan'),
                Tables\Columns\TextColumn::make('up_sertifikat')
                ->label('Dokumen Sertifikat')
                ->url(fn ($record) => $record->up_sertifikat ? Storage::url($record->up_sertifikat) : '#', true)
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('up_pbb')
                ->label('Dokumen PBB')
                ->url(fn ($record) => $record->up_pbb ? Storage::url($record->up_pbb) : '#', true)
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('up_img')
                ->label('Dokumen IMG')
                ->url(fn ($record) => $record->up_img ? Storage::url($record->up_img) : '#', true)
                ->sortable()
                ->searchable(),
            ])
            ->defaultSort('siteplan', 'asc')
            ->headerActions([
                Action::make('count')
                    ->label(fn ($livewire): string => 'Total: ' . $livewire->getFilteredTableQuery()->count())
                    ->disabled(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                ->label('Data yang dihapus') 
                ->native(false),

                Filter::make('status_sertifikat')
                    ->label('Status Sertifikat')
                    ->form([
                        Select::make('status_sertifikat')
                            ->options([
                                'induk' => 'Induk',
                                'pecahan' => 'Pecahan',
                            ])
                            ->nullable()
                            ->native(false),
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when(isset($data['status_sertifikat']), fn ($q) =>
                            $q->where('status_sertifikat', $data['status_sertifikat'])
                        )
                    ),
            
                    Filter::make('created_from')
                    ->label('Dari Tanggal')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari')
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when($data['created_from'] ?? null, fn ($q) =>
                            $q->whereDate('created_at', '>=', $data['created_from'])
                        )
                    ),
                
                Filter::make('created_until')
                    ->label('Sampai Tanggal')
                    ->form([
                        DatePicker::make('created_until')
                            ->label('Sampai')
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when($data['created_until'] ?? null, fn ($q) =>
                            $q->whereDate('created_at', '<=', $data['created_until'])
                        )
                    ),                
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormMaxHeight('400px')
            ->filtersFormColumns(4)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)

        
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('success')
                        ->label('Lihat'),
                    EditAction::make()
                        ->color('info')
                        ->label('Ubah')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data KPR Diubah')
                                ->body('Data KPR telah berhasil disimpan.')),                    
                        DeleteAction::make()
                        ->color('danger')
                        ->label('Hapus')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data KPR Dihapus')
                                ->body('Data KPR telah berhasil dihapus.')),
                    // RestoreAction::make()
                    //     ->label('Pulihkan')
                    //     ->successNotificationTitle('Data berhasil dipulihkan')
                    //     ->successRedirectUrl(route('filament.admin.resources.audits.index')),
                    Tables\Actions\RestoreAction::make()
                    ->color('info')
                    ->label('Kembalikan Data')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data KPR')
                            ->body('Data KPR berhasil dikembalikan.')
                    ),
                    Tables\Actions\ForceDeleteAction::make()
                    ->color('primary')
                    ->label('Hapus Permanen')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data KPR')
                            ->body('Data KPR berhasil dihapus secara permanen.')
                    ),
                    ])->button()->label('Action'),
                ], position: ActionsPosition::BeforeCells)
            
                ->groupedBulkActions([
                    BulkAction::make('delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash') 
                        ->color('danger')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data KPR')
                                ->body('Data KPR berhasil dihapus.'))                        
                                ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),
                
                    BulkAction::make('forceDelete')
                        ->label('Hapus Permanent')
                        ->icon('heroicon-o-x-circle') 
                        ->color('warning')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data KPR')
                                ->body('Data KPR berhasil dihapus secara permanen.'))                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->forceDelete()),
                
                    BulkAction::make('export')
                        ->label('Download Data')
                        ->icon('heroicon-o-arrow-down-tray') 
                        ->color('info')
                        ->action(fn (Collection $records) => static::exportData($records)),
                
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Kembalikan Data')
                        ->icon('heroicon-o-arrow-path') 
                        ->color('success')
                        ->button()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data KPR')
                                ->body('Data KPR berhasil dikembalikan.')),
                ]);
    }

    public static function exportData(Collection $records)
    {
        $csvData = "ID, Jenis Unit, Blok, Type, Luas, Agent, Tanggal Booking, Tanggal Akad, Harga, Maksimal KPR, Nama Konsumen, NIK, NPWP, Alamat, NO Handphone, Email, Pembayaran, Bank, No. Rekening, Status Akad\n";
    
        foreach ($records as $record) {
            $csvData .= "{$record->id}, {$record->jenis_unit}, {$record->siteplan}, {$record->type}, {$record->luas}, {$record->agent}, {$record->tanggal_booking}, {$record->tanggal_akad}, {$record->harga}, {$record->maksimal_kpr}, {$record->nama_konsumen}, {$record->nik}, {$record->npwp}, {$record->alamat}, {$record->no_hp}, {$record->no_email}, {$record->pembayaran}, {$record->bank}, {$record->no_rekening}, {$record->status_akad}\n";
        }
    
        return response()->streamDownload(fn () => print($csvData), 'dataKPR.csv');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormLegals::route('/'),
            'create' => Pages\CreateFormLegal::route('/create'),
            'edit' => Pages\EditFormLegal::route('/{record}/edit'),
        ];
    }
}
