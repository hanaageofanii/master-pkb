<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormLegalPcaResource\Pages;
use App\Filament\Resources\FormLegalPcaResource\RelationManagers;
use App\Models\form_legal_pca;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GCVResource;
use App\Models\GCV;
use App\Models\form_kpr_pca;
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
use Illuminate\Support\Facades\Auth;

class FormLegalPcaResource extends Resource
{
    protected static ?string $model = form_legal_pca::class;

    protected static ?string $title = "Legalitas PCA";
    protected static ?string $navigationGroup = "Legal";
    protected static ?string $pluralLabel = "Form Legalitas PCA";
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'Sertifikat PCA';
    protected static ?string $pluralModelLabel = 'Daftar Legalitas PCA';
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
                Forms\Components\Select::make('siteplan')
                    ->label('Blok')
                    ->nullable()
                    ->options(fn ($get, $set, $record) =>
                        form_kpr_pca::where('status_akad', 'akad')
                            ->pluck('siteplan', 'siteplan')
                            ->toArray()
                        + ($record?->siteplan ? [$record->siteplan => $record->siteplan] : [])
                    )
                    ->reactive()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->afterStateUpdated(function ($state, callable $set) {
                        $gcv = form_kpr_pca::where('siteplan', $state)->first();

                        if ($gcv) {
                            $set('nama_konsumen', $gcv->nama_konsumen);
                        }
                    }),

                    Forms\Components\TextInput::make('nama_konsumen')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('Nama Konsumen'),

                    Forms\Components\TextInput::make('id_rumah')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('No. ID Rumah'),

                    Forms\Components\Select::make('status_sertifikat')
                    ->label('Status Sertifikat')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->options([
                        'induk' => 'Induk',
                        'pecahan' => 'Pecahan',
                    ])->nullable(),

                    Forms\Components\TextInput::make('no_sertifikat')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('No. Sertifikat'),
                    Forms\Components\TextInput::make('nib')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->nullable()
                    ->label('NIB'),

                    Forms\Components\TextInput::make('luas_sertifikat')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('Luas Sertifikat'),

                    Forms\Components\TextInput::make('imb_pbg')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('IMB/PBG'),

                    Forms\Components\TextInput::make('nop')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->label('NOP'),

                    Forms\Components\TextInput::make('nop1')
                    ->nullable()
                    ->label('NOP Tambahan')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Legal officer']);
                    })())
                    ->helperText('Jika irisan input data 2X'),

                    Forms\Components\Fieldset::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('up_sertifikat')
                            ->disk('public')
                            ->disabled(fn () => ! (function () {
                                /** @var \App\Models\User|null $user */
                                $user = Auth::user();
                                return $user && $user->hasRole(['admin','Legal officer']);
                            })())
                            ->nullable()
                            ->multiple()
                            ->label('Sertifikat')
                            ->downloadable()
                            ->previewable(false),

                        Forms\Components\FileUpload::make('up_pbb')
                            ->disk('public')
                            ->disabled(fn () => ! (function () {
                                /** @var \App\Models\User|null $user */
                                $user = Auth::user();
                                return $user && $user->hasRole(['admin','Legal officer']);
                            })())
                            ->nullable()
                            ->multiple()
                            ->label('PBB')
                            ->downloadable()
                            ->previewable(false),

                        Forms\Components\FileUpload::make('up_img')
                            ->disk('public')
                            ->disabled(fn () => ! (function () {
                                /** @var \App\Models\User|null $user */
                                $user = Auth::user();
                                return $user && $user->hasRole(['admin','Legal officer']);
                            })())
                            ->nullable()
                            ->multiple()
                            ->label('IMG')
                            ->downloadable()
                            ->previewable(false),
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
                Tables\Columns\TextColumn::make('status_sertifikat')
                ->sortable()
                ->searchable()
                ->label('Status Sertifikat')
                ->formatStateUsing(fn ($state) => match ($state) {
                        'induk' => 'Induk',
                        'pecahan' => 'Pecahan',
                default => $state,
                }),

                Tables\Columns\TextColumn::make('no_sertifikat')->sortable()->searchable()->label('No. Sertifikat'),
                Tables\Columns\TextColumn::make('luas_sertifikat')->sortable()->searchable()->label('Luas Sertifikat'),
                Tables\Columns\TextColumn::make('nop')->sortable()->searchable()->label('NOP'),
                Tables\Columns\TextColumn::make('nop1')->sortable()->searchable()->label('NOP Tambahan'),

                TextColumn::make('up_sertifikat')
                ->label('Sertifikat')
                ->formatStateUsing(function ($record) {
                    if (!$record->up_sertifikat) {
                        return 'Tidak Ada Dokumen';
                    }

                    $files = is_array($record->up_sertifikat) ? $record->up_sertifikat : json_decode($record->up_sertifikat, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $files = [];
                    }

                    $output = '';
                    foreach ($files as $file) {
                        $url = Storage::url($file);
                        $output .= '<a href="' . $url . '" target="_blank">Lihat</a> | <a href="' . $url . '" download>Download</a><br>';
                    }

                    return $output ?: 'Tidak Ada Dokumen';
                })
                ->html()
                ->sortable(),

                TextColumn::make('up_pbb')
                ->label('PBB')
                ->formatStateUsing(function ($record) {
                    if (!$record->up_pbb) {
                        return 'Tidak Ada Dokumen';
                    }

                    $files = is_array($record->up_pbb) ? $record->up_pbb : json_decode($record->up_pbb, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $files = [];
                    }

                    $output = '';
                    foreach ($files as $file) {
                        $url = Storage::url($file);
                        $output .= '<a href="' . $url . '" target="_blank">Lihat</a> | <a href="' . $url . '" download>Download</a><br>';
                    }

                    return $output ?: 'Tidak Ada Dokumen';
                })
                ->html()
                ->sortable(),

                TextColumn::make('up_img')
                ->label('IMG')
                ->formatStateUsing(function ($record) {
                    if (!$record->up_img) {
                        return 'Tidak Ada Dokumen';
                    }

                    $files = is_array($record->up_img) ? $record->up_img : json_decode($record->up_img, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $files = [];
                    }

                    $output = '';
                    foreach ($files as $file) {
                        $url = Storage::url($file);
                        $output .= '<a href="' . $url . '" target="_blank">Lihat</a> | <a href="' . $url . '" download>Download</a><br>';
                    }

                    return $output ?: 'Tidak Ada Dokumen';
                })
                ->html()
                ->sortable(),

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
                                ->title('Data Sertifikat Diubah')
                                ->body('Data Sertifikat telah berhasil disimpan.')),
                                DeleteAction::make()
                                ->color('danger')
                                ->label(fn ($record) => "Hapus Blok{$record->siteplan}")
                                ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok {$record->siteplan}")
                                ->modalDescription(fn ($record) => "Apakah Anda yakin ingin menghapus blok {$record->siteplan}?")
                                ->successNotification(
                                    Notification::make()
                                        ->success()
                                        ->title('Data Sertifikat Dihapus')
                                        ->body('Data Sertifikat telah berhasil dihapus.')),
                    // RestoreAction::make()
                    //     ->label('Pulihkan')
                    //     ->successNotificationTitle('Data berhasil dipulihkan')
                    //     ->successRedirectUrl(route('filament.admin.resources.audits.index')),
                    Tables\Actions\RestoreAction::make()
                    ->color('info')
                    ->label(fn ($record) => "Kembalikan {$record->siteplan}")
                    ->modalHeading(fn ($record) => "Konfirmasi Kembalikan Blok{$record->siteplan}")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengembalikan blok {$record->siteplan}?")
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data Sertifikat')
                            ->body('Data Sertifikat berhasil dikembalikan.')
                    ),
                    Tables\Actions\ForceDeleteAction::make()
                    ->color('primary')
                    ->label(fn ($record) => "Hapus Permanent {$record->siteplan}")
                    ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok Permanent{$record->siteplan}")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengahapus blok secara permanent {$record->siteplan}?")
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data Sertifikat')
                            ->body('Data Sertifikat berhasil dihapus secara permanen.')
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
                                ->title('Data Sertifikat')
                                ->body('Data Sertifikat berhasil dihapus.'))
                                ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),

                    BulkAction::make('forceDelete')
                        ->label('Hapus Permanent')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data Sertifikat')
                                ->body('Data Sertifikat berhasil dihapus secara permanen.'))                        ->requiresConfirmation()
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
                                ->title('Data Sertifikat')
                                ->body('Data Sertifikat berhasil dikembalikan.')),
                ]);
    }

    public static function exportData(Collection $records)
    {
        $csvData = "ID, Blok, Nama Konsumen, ID Rumah, Status Sertifikat, No. Sertifikat, Luas Sertifikat, NOP, NOP Pecahan\n";

        foreach ($records as $record) {
            $csvData .= "{$record->id}, {$record->siteplan}, {$record->nama_konsumen}, {$record->id_rumah}, {$record->status_sertifikat}, {$record->no_sertifikat}, {$record->luas_sertifikat}, {$record->nop}, {$record->nop1}\n";
        }

        return response()->streamDownload(fn () => print($csvData), 'Sertifikat.csv');
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
            'index' => Pages\ListFormLegalPcas::route('/'),
            'create' => Pages\CreateFormLegalPca::route('/create'),
            'edit' => Pages\EditFormLegalPca::route('/{record}/edit'),
        ];
    }
}
