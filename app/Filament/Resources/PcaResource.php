<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PcaResource\Pages;
use App\Filament\Resources\PcaResource\RelationManagers;
use App\Models\Pca;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\AuditPCA;
use App\Filament\Resources\AuditPcaResource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\TrashedFilter;
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
use App\Filament\Resources\PCAResource\Widgets\PCAStats;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PcaResource extends Resource
{
    protected static ?string $model = Pca::class;

    protected static ?string $title = "Pesona Cengkong Asri";
    protected static ?string $navigationGroup = "Stok";
    protected static ?string $pluralLabel = "PCA";
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'PCA';
    protected static ?string $pluralModelLabel = 'Data PCA';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proyek')
                    ->options([
                        'gcv_cira' => 'GCV Cira',
                        'gcv' => 'GCV',
                        'tkr' => 'TKR',
                        'pca1' => 'PCA1',
                    ])
                    ->label('Proyek')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('admin');
                    })()),

                Forms\Components\Select::make('nama_perusahaan')
                    ->label('Nama Perumahan')
                    ->options([
                        'grand_cikarang_village' => 'Grand Cikarang Village',
                        'taman_kertamukti_residence' => 'Taman Kertamukti Residence',
                        'pesona_cengkong_asri_1' => 'Pesona Cengkong Asri 1',
                    ])
                    ->label('Nama Perusahaan')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('admin');
                    })()),

                Forms\Components\Select::make('kavling')
                    ->options([
                        'standar' => 'Standar',
                        'khusus' => 'Khusus',
                        'hook' => 'Hook',
                        'komersil' => 'Komersil',
                        'tanah_lebih' => 'Tanah Lebih',
                        'kios' => 'Kios'
                    ])
                    ->label('Kavling')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('admin');
                    })()),

                    Forms\Components\Select::make('siteplan')
                        ->label('Blok')
                        ->options(
                            AuditPCA::where('terbangun', '=', 1)
                                ->pluck('siteplan', 'siteplan')
                                ->toArray()
                                )
                                ->searchable()
                        ->required()
                        ->reactive()
                        ->unique(ignoreRecord: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $audit = AuditPCA::where('siteplan', $state)->first();

                            if ($audit) {
                                $set('type', $audit->type);
                            }
                        })->disabled(fn () => ! (function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            return $user && $user->hasRole('admin');
                        })()),


                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('admin');
                    })()),


                Forms\Components\TextInput::make('luas_tanah')
                    ->numeric()
                    ->label('Luas Tanah')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole('admin');
                    })()),

                Forms\Components\Select::make('status')
                    ->options([
                        'booking' => 'Booking',
                    ])
                    ->label('Status')
                    ->afterStateUpdated(function ($state, $set, $record) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (is_null($state) && $user && $user->hasRole(['Direksi', 'Super admin','admin'])) {
                            $set('tanggal_booking', null);
                            $set('nama_konsumen', null);
                            $set('agent', null);

                            $record->update([
                                'tanggal_booking' => null,
                                'nama_konsumen' => null,
                                'agent' => null
                            ]);
                        }
                    })
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['Direksi', 'Super admin','admin','Legal officer','Legal Pajak','KPR Stok']);
                    })()),
                    // ->required(),

                Forms\Components\DatePicker::make('tanggal_booking')
                    ->label('Tanggal Booking')
                    ->disabled(fn ($get) => ! (function () use ($get) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin', 'Legal officer','Legal Pajak', 'KPR Stok']) && $get('status') === 'booking';
                    })()),

                Forms\Components\TextInput::make('nama_konsumen')
                    ->label('Nama Konsumen')
                    ->disabled(fn ($get) => ! (function () use ($get) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin', 'Legal officer','Legal Pajak', 'KPR Stok']) && $get('status') === 'booking';
                    })()),

                Forms\Components\TextInput::make('agent')
                    ->label('Agent')
                    ->disabled(fn ($get) => ! (function () use ($get) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin', 'Legal officer','Legal Pajak', 'KPR Stok']) && $get('status') === 'booking';
                    })()),

                    Forms\Components\Select::make('status_sertifikat')
                    ->options([
                        'pecah' => 'SUDAH PECAH',
                        'belum' => 'BELUM PECAH',
                    ])
                    ->label('Status Sertifikat')
                     ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['Legal officer']);
                    })()),

                    Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'cash' => 'CASH',
                        'kpr' => 'KPR',
                        'cash_bertahap' => 'CASH BERTAHAP',
                        'promo' => 'PROMO',
                    ])
                    ->label('Status Pembayaran')
                     ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','KPR Stok']);
                    })()),

                Forms\Components\Select::make('kpr_status')
                    ->options([
                        'sp3k' => 'SP3K',
                        'akad' => 'Akad',
                        'batal' => 'Batal',
                    ])
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($record && $record->siteplan) {
                            $audit = \App\Models\AuditPCA::where('siteplan', $record->siteplan)->first();

                            if ($audit) {
                                $audit->update([
                                    'status' => $state === 'akad' ? 'akad' : null,
                                ]);
                            }
                        }
                    })
                    ->afterStateUpdated(function ($state, $set, $record) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (is_null($state) && $user && $user->hasRole(['Direksi', 'Super admin','admin','KPR Stok'])) {
                            $set('tanggal_akad', null);


                            $record->update([
                                'tanggal_akad' => null,
                            ]);
                        }
                    })
                    ->label('KPR Status')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','KPR officer']);
                    })()),

                    Forms\Components\DatePicker::make('tanggal_akad')
                    ->label('Tanggal Akad')
                    ->disabled(fn ($get) => ! (function () use ($get) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin', 'KPR officer']) && $get('kpr_status') === 'akad';
                    })()),

                Forms\Components\Textarea::make('ket')
                    ->label('Keterangan')
                    ->nullable()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','KPR officer']);
                    })()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proyek')->label('Proyek')
                ->formatStateUsing(fn (string $state): string => match ($state)
                {
                        'gcv_cira' => 'GCV Cira',
                        'gcv' => 'GCV',
                        'tkr' => 'TKR',
                        'pca1' => 'PCA1',
                        default => $state,
                })->searchable()
                ->disabled(fn () => ! (function () {
                    /** @var \App\Models\User|null $user */
                    $user = Auth::user();
                    return $user && $user->hasRole('admin');
                })()),
                Tables\Columns\TextColumn::make('nama_perusahaan')->label('Nama Perumahan')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'grand_cikarang_village' => 'Grand Cikarang Village',
                    'taman_kertamukti_residence' => 'Taman Kertamukti Residence',
                    'pesona_cengkong_asri_1' => 'Pesona Cengkong Asri 1',
                    default => $state,
                })->searchable(),

                Tables\Columns\TextColumn::make('kavling')->label('Kavling')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'standar' => 'Standar',
                    'khusus' => 'Khusus',
                    'hook' => 'Hook',
                    'komersil' => 'Komersil',
                    'tanah_lebih' => 'Tanah Lebih',
                    'kios' => 'Kios',
                    default => $state,
                })->searchable(),

                Tables\Columns\TextColumn::make('siteplan')->label('Blok')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('luas_tanah')->label('Luas Tanah')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'booking' => 'Booking',
                    default => $state,
                })->searchable(),

                Tables\Columns\TextColumn::make('tanggal_booking')->date()->label('Tanggal Booking')->searchable()                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),
                Tables\Columns\TextColumn::make('nama_konsumen')->label('Nama Konsumen')->searchable(),
                Tables\Columns\TextColumn::make('agent')->label('Agent')->searchable(),
                 Tables\Columns\TextColumn::make('status_sertifikat')->label('Status Sertifikat')
            ->formatStateUsing(fn (string $state): string => match ($state) {
                'pecah' => 'SUDAH PECAH',
                'belum' => 'BELUM PECAH',
                default => $state,
            })->searchable(),
            Tables\Columns\TextColumn::make('status_pembayaran')->label('Status Pembayaran')
            ->formatStateUsing(fn (string $state): string => match ($state) {
                'cash' => 'CASH',
                'kpr' => 'KPR',
                'cash_bertahap' => 'CASH BERTAHAP',
                'promo' => 'PROMO',
                default => $state,
            })->searchable(),
                Tables\Columns\TextColumn::make('kpr_status')
                    ->label('KPR Status')
                    ->default(fn ($record) => $record->audit_p_c_a_s?->status === 'akad' ? 'Akad' : $record->kpr_status)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sp3k' => 'SP3K',
                        'akad' => 'Akad',
                        'batal' => 'Batal',
                        default => $state,
                    })->searchable(),
                Tables\Columns\TextColumn::make('tanggal_akad')->date()->label('Tanggal Akad')->searchable()                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),

            ])
            ->defaultSort('siteplan', 'asc')
            ->headerActions([
                Action::make('count')
                ->label(fn ($livewire): string => 'Total: ' . $livewire->getFilteredTableQuery()->count())
                ->disabled(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'booking' => 'Booking',
                    ]) ->native(false),

                Tables\Filters\TrashedFilter::make()
                    ->label('Data yang dihapus')
                    ->native(false),



                Tables\Filters\SelectFilter::make('kpr_status')
                    ->label('Status KPR')
                    ->options([
                        'sp3k' => 'SP3K',
                        'akad' => 'Akad',
                        'batal' => 'Batal',
                    ])
                    ->native(false),


                Tables\Filters\SelectFilter::make('proyek')
                    ->label('Proyek')
                    ->options([
                        'pca_cira' => 'PCA Cira',
                        'pca' => 'PCA',
                    ])
                    ->native(false),

                Filter::make('created_from')
                    ->label('Dari Tanggal')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari'),
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
                            ->label('Sampai'),
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
                                ->title('Data PCA Diperbarui')
                                ->body('Data PCA telah berhasil disimpan.')),
                                DeleteAction::make()
                                ->color('danger')
                                ->label(fn ($record) => "Hapus Blok {$record->siteplan}")
                                ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok {$record->siteplan}")
                                ->modalDescription(fn ($record) => "Apakah Anda yakin ingin menghapus blok {$record->siteplan}?")
                                ->successNotification(
                                    Notification::make()
                                        ->success()
                                        ->title('Data PCA Dihapus')
                                        ->body('Data PCA telah berhasil dihapus.')),
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
                            ->title('Data PCA')
                            ->body('Data PCA berhasil dikembalikan.')
                    ),
                    Tables\Actions\ForceDeleteAction::make()
                    ->color('primary')
                    ->label(fn ($record) => "Hapus Permanent {$record->siteplan}")
                    ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok Permanent{$record->siteplan}")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengahapus blok secara permanent {$record->siteplan}?")
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data PCA')
                            ->body('Data PCA berhasil dihapus secara permanen.')
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
                                ->title('Data PCA')
                                ->body('Data PCA berhasil dihapus.'))
                                ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),

                    BulkAction::make('forceDelete')
                        ->label('Hapus Permanent')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data PCA')
                                ->body('Data PCA berhasil dihapus secara permanen.'))                        ->requiresConfirmation()
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
                                ->title('Data PCA')
                                ->body('Data PCA berhasil dikembalikan.')),
                ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function exportData(Collection $records)
    {
        $csvData = "ID, Proyek, Nama Perumahan, Kavling, Siteplan/Blok, Type, Luas Tanah, Status, Tanggal Booking, Nama Konsumen, Agent, Status KPR, Keterangan, User, Tanggal Update\n";

        foreach ($records as $record) {
            $csvData .= "{$record->id}, {$record->proyek}, {$record->nama_perusahaan}, {$record->kavling}, {$record->siteplan}, {$record->type}, {$record->luas_tanah}, {$record->status}, {$record->tanggal_booking}, {$record->nama_konsumen}, {$record->agent}, {$record->kpr_status}, {$record->ket}, {$record->user}, {$record->tanggal_update}\n";
        }

        return response()->streamDownload(fn () => print($csvData), 'PCA.csv');
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);

    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if ($user) {
        if ($user->hasRole('Marketing')) {
            $query->where(function ($q) {
                $q->whereNull('kpr_status')
                    ->orWhere('kpr_status', '!=', 'akad');
            });
        } elseif ($user->hasRole(['Legal officer','Legal Pajak'])) {
            $query->where('kpr_status', 'akad');
        }
    }

    return $query;
}


    public static function getWidgets(): array
    {
        return [
            PCAStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPcas::route('/'),
            'create' => Pages\CreatePca::route('/create'),
            'edit' => Pages\EditPca::route('/{record}/edit'),
        ];
    }
}
