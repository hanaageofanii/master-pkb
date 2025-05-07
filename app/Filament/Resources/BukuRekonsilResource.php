<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BukuRekonsilResource\Pages;
use App\Filament\Resources\BukuRekonsilResource\RelationManagers;
use App\Models\BukuRekonsil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Models\Rekening;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;

class BukuRekonsilResource extends Resource
{
    protected static ?string $model = BukuRekonsil::class;

    // protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = "Kasir";
    protected static ?string $pluralLabel = "Buku Rekonsil";
    protected static ?string $navigationLabel = "Buku Rekonsil";
    protected static ?string $pluralModelLabel = 'Daftar Buku Rekonsil';
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make()
                ->schema([
                    Select::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->options([
                        'langgeng_pertiwi_development' => 'PT. Langgeng Pertiwi Development',
                        'agung_purnama_bakti' => 'PT. Agung Purnama Bakti',
                        'purnama_karya_bersama' => 'PT. Purnama Karya Bersama',
                    ])
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('bank', null)) // reset bank saat perusahaan berubah
                    ->required()
                ->disabled(fn () => ! (function () {
                    /** @var \App\Models\User|null $user */
                    $user = Auth::user();
                    return $user && $user->hasRole(['admin','Kasir 1','Kasir 2']);
                })()),

                    TextInput::make('no_check')
                    ->label('No. Check')
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })()),
                    // ->unique(ignoreRecord: true),

                    DatePicker::make('tanggal_check')
                    ->label('Tanggal Check')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    TextInput::make('nama_pencair')
                    ->label(' Nama Pencair')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    DatePicker::make('tanggal_dicairkan')
                    ->label('Tanggal di Cairkan')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    TextInput::make('nama_penerima')
                    ->label(' Nama Penerima')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    TextInput::make('account_bank')
                    ->label('Akun Bank Penerima')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    Select::make('bank')
                    ->label('Bank')
                    ->options(function (callable $get) {
                        // Get the selected 'nama_perusahaan' value
                        $perusahaan = $get('nama_perusahaan');
                        
                        return Rekening::where('nama_perusahaan', $perusahaan)
                            ->pluck('bank', 'bank') 
                            ->unique() 
                            ->map(function ($item) {
                                return strtoupper(str_replace('_', ' ', $item)); 
                            })
                            ->toArray(); 
                    })
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $data = Rekening::where('nama_perusahaan', $get('nama_perusahaan'))
                            ->where('bank', $get('bank'))
                            ->first();
                
                        if ($data) {
                            $set('jenis', $data->jenis); 
                            $set('rekening', $data->rekening); 
                        } else {
                            $set('jenis', null);  
                            $set('rekening', null); 
                        }
                    })
                    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })()),
                

    Select::make('jenis')
    ->label('Jenis')
    ->options([
        'operasional' => 'Operasional',
        'escrow' => 'Escrow',
           ])
    ->options(function (callable $get) {
        $perusahaan = $get('nama_perusahaan');
        $bank = $get('bank');
        return Rekening::where('nama_perusahaan', $perusahaan)
            ->where('bank', $bank)
            ->pluck('jenis', 'jenis')
            ->unique()
            ->map(function ($item) {
                return ucfirst(strtolower($item));
            })
            ->toArray(); 
    })
    ->reactive() 
    ->afterStateUpdated(function (callable $get, callable $set) {
        $data = Rekening::where('nama_perusahaan', $get('nama_perusahaan'))
            ->where('bank', $get('bank'))
            ->where('jenis', $get('jenis'))
            ->first();

        if ($data) {
            $set('rekening', $data->rekening);
        } else {
            $set('rekening', null);  
        }
    })
    ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 1','Kasir 2']);
                    })()),

                    TextInput::make('rekening')
                ->label('No. Rekening')
                ->required()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 1','Kasir 2']);
                    })()),

                    TextInput::make('deskripsi')
                    ->label(' Deskripsi')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    TextInput::make('jumlah_uang')
                        ->label('Jumlah Uang')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $perusahaan = $get('nama_perusahaan');
                            $tipe = $get('tipe');
                            $jumlahUang = (int) $get('jumlah_uang');

                            if (! $perusahaan || ! $tipe || $jumlahUang === null) {
                                return;
                            }

                            $saldoSebelumnya = \App\Models\BukuRekonsil::where('nama_perusahaan', $perusahaan)
                                ->selectRaw("SUM(CASE WHEN tipe = 'debit' THEN jumlah_uang ELSE -jumlah_uang END) as total")
                                ->value('total') ?? 0;

                            // Hitung saldo baru
                            $saldoBaru = $tipe === 'debit'
                                ? $saldoSebelumnya + $jumlahUang
                                : $saldoSebelumnya - $jumlahUang;

                            $set('saldo', $saldoBaru);
                        })
                        ->disabled(fn () => ! (function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                        })()),

                        Select::make('tipe')
                        ->label('Tipe')
                        ->options([
                            'debit' => 'Debit',
                            'kredit' => 'Kredit',
                        ])
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $perusahaan = $get('nama_perusahaan');
                            $tipe = $get('tipe');
                            $jumlahUang = (int) $get('jumlah_uang');
                    
                            if (! $perusahaan || ! $tipe || $jumlahUang === null) {
                                return;
                            }
                    
                            // Hitung total saldo perusahaan
                            $saldoSebelumnya = \App\Models\BukuRekonsil::where('nama_perusahaan', $perusahaan)
                                ->selectRaw("SUM(CASE WHEN tipe = 'debit' THEN jumlah_uang ELSE -jumlah_uang END) as total")
                                ->value('total') ?? 0;
                    
                            $saldoBaru = $tipe === 'debit'
                                ? $saldoSebelumnya + $jumlahUang
                                : $saldoSebelumnya - $jumlahUang;
                    
                            $set('saldo', $saldoBaru);
                        })
                        ->disabled(fn () => ! (function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                        })())
                        ->required(),
                    

                    TextInput::make('saldo')
                    ->label('Saldo')
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })()),

                    Select::make('status_disalurkan')
                    ->label('Status di Salurkan')
                    ->options([
                        'sudah' => 'Sudah',
                        'belum' => 'Belum',
                    ])
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 2','Kasir 1']);
                    })())
                    ->required(),

                    Forms\Components\FileUpload::make('bukti_bukti')
                    ->disk('public')
                    ->nullable()
                    ->multiple()
                    ->disabled(fn () => ! (function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasRole(['admin','Kasir 1', 'Kasir 2']);
                    })())
                    ->label('Bukti - Bukti')
                    ->downloadable()
                    ->previewable(false),
            ])

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_perusahaan')->label('Nama Perusahaan')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'langgeng_pertiwi_development' => 'PT. Langgeng Pertiwi Development',
                    'agung_purnama_bakti' => 'PT. Agung Purnama Bakti',
                    'purnama_karya_bersama' => 'PT. Purnama Karya Bersama',
                    default => $state,
                })->searchable(),

                Tables\Columns\TextColumn::make('no_check')->label('No. Check')
                ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_check')->label('Tanggal Check')
                ->searchable()                
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),

                Tables\Columns\TextColumn::make('nama_pencair')->label('Nama Pencair')
                ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_dicairkan')->label('Tanggal di Cairkan')
                ->searchable()                
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),

                Tables\Columns\TextColumn::make('nama_penerima')->label('Nama Penerima')
                ->searchable(),

                Tables\Columns\TextColumn::make('account_bank')->label('Account Bank Penerima')
                ->searchable(),

                Tables\Columns\TextColumn::make('bank')->label('Bank')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'btn_karawang' => 'BTN Karawang',
                    'btn_cikarang' => 'BTN Cikarang',
                    'btn_bekasi' => 'BTN Bekasi',
                    'bjb_cikarang' => 'BJB Cikarang',
                    'bri_pekayon' => 'BRI Pekayon',
                    'bjb_syariah' => 'BJB Syariah',
                    'btn_cibubur' => 'BTN Cibubur',
                    'bni_kuningan' => 'BNI Kuningan',
                    'mandiri_cikarang' => 'Mandiri Cikarang',
                    default => $state,
                })->searchable(),

                Tables\Columns\TextColumn::make('rekening')->label('No. Rekening')
                ->searchable(),

                Tables\Columns\TextColumn::make('deskripsi')->label('Deskripsi')
                ->searchable(),

                Tables\Columns\TextColumn::make('jumlah_uang')->label('Jumlah Uang')
                ->searchable()->prefix('Rp. '),

                Tables\Columns\TextColumn::make('tipe')->label('Tipe')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                   'sudah' => 'Sudah',
                   'belum' => 'Belum',
                    default => $state,
                })->searchable(),

                
                Tables\Columns\TextColumn::make('saldo')->label('Saldo')
                ->searchable()->prefix('Rp. '),

                Tables\Columns\TextColumn::make('status_disalurkan')->label('Status di Salurkan')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                   'sudah' => 'Sudah',
                   'belum' => 'Belum',
                    default => $state,
                })->searchable(),

                TextColumn::make('bukti_bukti')
                ->label('Bukti - Bukti')
                ->formatStateUsing(function ($record) {
                    if (!$record->up_kode_billing) {
                        return 'Tidak Ada Dokumen';
                    }

                    $files = is_array($record->up_kode_billing) ? $record->up_kode_billing : json_decode($record->up_kode_billing, true);

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
            ->filters([
                Tables\Filters\SelectFilter::make('status_disalurkan')
                    ->label('Status di Salurkan')
                    ->options([
                        'sudah' => 'Sudah',
                        'belum' => 'Belum',                                           
                        ]) ->native(false),
            
                Tables\Filters\TrashedFilter::make()
                    ->label('Data yang dihapus')
                    ->native(false),

                Tables\Filters\SelectFilter::make('nama_perusahaan') 
                    ->label('Nama Perusahaan')
                    ->options([
                        'langgeng_pertiwi_development' => 'PT. Langgeng Pertiwi Development',
                        'agung_purnama_bakti' => 'PT. Agung Purnama Bakti',
                        'purnama_karya_bersama' => 'PT. Purnama Karya Bersama',
                    ])
                    ->native(false),


                Tables\Filters\SelectFilter::make('tipe') 
                    ->label('Tipe')
                    ->options([
                        'debit' => 'Debit',
                        'kredit' => 'Kredit',
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
                                ->title('Data Rekonsil Diperbarui')
                                ->body('Data Rekonsil telah berhasil disimpan.')),                    
                                DeleteAction::make()
                                ->color('danger')
                                ->label(fn ($record) => "Hapus Blok {$record->no_check}")
                                ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok {$record->no_check}")
                                ->modalDescription(fn ($record) => "Apakah Anda yakin ingin menghapus blok {$record->no_check}?")
                                ->successNotification(
                                    Notification::make()
                                        ->success()
                                        ->title('Data Rekonsil Dihapus')
                                        ->body('Data Rekonsil telah berhasil dihapus.')),
                    // RestoreAction::make()
                    //     ->label('Pulihkan')
                    //     ->successNotificationTitle('Data berhasil dipulihkan')
                    //     ->successRedirectUrl(route('filament.admin.resources.audits.index')),
                    Tables\Actions\RestoreAction::make()
                    ->color('info')
                    ->label(fn ($record) => "Kembalikan {$record->no_check}")
                    ->modalHeading(fn ($record) => "Konfirmasi Kembalikan Blok{$record->no_check}")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengembalikan blok {$record->no_check}?")
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data Rekonsil')
                            ->body('Data Rekonsil berhasil dikembalikan.')
                    ),
                    Tables\Actions\ForceDeleteAction::make()
                    ->color('primary')
                    ->label(fn ($record) => "Hapus Permanent {$record->no_check}")
                    ->modalHeading(fn ($record) => "Konfirmasi Hapus Blok Permanent{$record->no_check}")
                    ->modalDescription(fn ($record) => "Apakah Anda yakin ingin mengahapus blok secara permanent {$record->no_check}?")
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data Rekonsil')
                            ->body('Data Rekonsil berhasil dihapus secara permanen.')
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
                                ->title('Data Rekonsil')
                                ->body('Data Rekonsil berhasil dihapus.'))                        
                                ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),
                
                    BulkAction::make('forceDelete')
                        ->label('Hapus Permanent')
                        ->icon('heroicon-o-x-circle') 
                        ->color('warning')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data Rekonsil')
                                ->body('Data Rekonsil berhasil dihapus secara permanen.'))                        ->requiresConfirmation()
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
                                ->title('Data Rekonsil')
                                ->body('Data Rekonsil berhasil dikembalikan.')),
                ]);
                
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function exportData(Collection $records)
    {
        $csvData = "Id, Nama Perusahaan, No. Check, Tanggal Check, Nama Pencair, Tanggal di Cairkan, Nama Penerima, Account Bank, Bank, Jenis, No. Rekening, Jumlah Uang, Tipe, Saldo, Status di Salurkan\n";
    
        foreach ($records as $record) {
            $csvData .= "{$record->id}, {$record->nama_perusahaan}, {$record->no_check}, {$record->tanggal_check}, {$record->nama_pencair}, {$record->tanggal_dicairkan}, {$record->nama_penerima}, {$record->account_bank}, {$record->bank}, {$record->jenis}, {$record->rekening}, {$record->deskripsi}, {$record->jumlah_uang}, {$record->tipe}, {$record->saldo}, {$record->status_disalurkan} \n";
        }
    
        return response()->streamDownload(fn () => print($csvData), 'BukuRekonsil.csv');
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);

    // /** @var \App\Models\User|null $user */
    // $user = Auth::user();

    // if ($user) {
    //     if ($user->hasRole('Marketing')) {
    //         $query->where(function ($q) {
    //             $q->whereNull('kpr_status')
    //                 ->orWhere('kpr_status', '!=', 'akad');
    //         });
    //     } elseif ($user->hasRole(['Legal officer','Legal Pajak'])) {
    //         $query->where('kpr_status', 'akad');
    //     }
    // }

    return $query;
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBukuRekonsils::route('/'),
            'create' => Pages\CreateBukuRekonsil::route('/create'),
            'edit' => Pages\EditBukuRekonsil::route('/{record}/edit'),
        ];
    }
}
