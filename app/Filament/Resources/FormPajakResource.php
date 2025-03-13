<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormPajakResource\Pages;
use App\Filament\Resources\FormPajakResource\RelationManagers;
use App\Models\form_kpr;
use App\Models\form_legal;
use App\Models\form_pajak;
use App\Models\FormPajak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\FormLegal;
use App\Filament\Resources\GCVResource;
use App\Models\GCV;
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

class FormPajakResource extends Resource
{
    protected static ?string $model = form_pajak::class;

    protected static ?string $title = "Form Validasi PPH";

    protected static ?string $navigationGroup = "Legal";

    protected static ?string $pluralLabel = "Data Validasi PPH";

    protected static ?string $navigationLabel = "Validasi PPH";

    protected static ?string $pluralModelLabel = 'Daftar Validasi PPH';
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('siteplan')
                ->label('Blok')
                ->nullable()
                ->options(fn() => form_kpr::pluck('siteplan', 'siteplan')->toArray())
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $kprData = form_kpr::where('siteplan', $state)->first();
                    if ($kprData) {
                        $set('kavling', $kprData->jenis_unit);
                        $set('nama_konsumen', $kprData->nama_konsumen);
                        $set('nik', $kprData->nik);
                        $set('npwp', $kprData->npwp);
                        $set('alamat', $kprData->alamat);
                        $set('harga', $kprData->harga);
                        $set('pembayaran', $kprData->pembayaran);
            
                        // Hitung NPOPTKP
                        $npoptkp = (int) $kprData->harga >= 80000000 ? 80000000 : 0;
                        $set('npoptkp', $npoptkp);
            
                        // Hitung BPHTB (5% dari harga - NPOPTKP)
                        $set('jumlah_bphtb', max(0.05 * ($kprData->harga - $npoptkp), 0));
            
                        // Tentukan Tarif PPH
                        $tarif_pph = ($kprData->jenis_unit === 'standar' && $kprData->pembayaran === 'kpr') ? 0.01 : 0.025;
                        $set('tarif_pph', ($tarif_pph * 100) . '%'); 
            
                        // Hitung Jumlah PPH
                        $jumlah_pph = max(($kprData->harga * $tarif_pph), 0);
                        $set('jumlah_pph', $jumlah_pph);
                    }
            

                        $legalData = form_legal::where('siteplan', $state)->first();
                        if ($legalData) {
                            $set('no_sertifikat', $legalData->no_sertifikat);
                            $set('nop', $legalData->nop);
                            $set('luas_tanah', $legalData->luas_sertifikat);
                        }
                    }),

            Forms\Components\TextInput::make('no_sertifikat')->nullable()->label('No. Sertifikat'),

            Forms\Components\Select::make('kavling')
                    ->options([
                        'standar' => 'Standar',
                        'khusus' => 'Khusus',
                        'hook' => 'Hook',
                        'komersil' => 'Komersil',
                        'tanah_lebih' => 'Tanah Lebih',
                        'kios' => 'Kios',
                    ])
                    ->required()
                    ->reactive()
                    ->label('Jenis Unit'),
                    
                Forms\Components\TextInput::make('nama_konsumen')->nullable()->label('Nama Konsumen'),

                Forms\Components\TextInput::make('nik')->nullable()->label('NIK'),
                Forms\Components\TextInput::make('npwp')->nullable()->label('NPWP'),
                Forms\Components\Textarea::make('alamat')->nullable()->label('Alamat'),
                Forms\Components\TextInput::make('nop')->nullable()->label('NOP'),
                Forms\Components\TextInput::make('luas_tanah')->nullable()->label('Luas Sertifikat'),
                Forms\Components\TextInput::make('harga')
                ->numeric()
                ->nullable()
                ->label('Harga')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $npoptkp = $state >= 80000000 ? 80000000 : 0;
                    $set('npoptkp', $npoptkp);
                    $set('jumlah_bphtb', max(0.05 * ($state - $npoptkp), 0));
                }),

                Forms\Components\TextInput::make('npoptkp')
                    ->numeric()
                    ->nullable()
                    ->label('NPOPTKP')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $harga = $get('harga');
                        $set('jumlah_bphtb', max(0.05 * ($harga - $state), 0));
                    }),

                Forms\Components\TextInput::make('jumlah_bphtb')->numeric()->nullable()->label('Jumlah BPHTB'),

                Forms\Components\Select::make('tarif_pph')
                ->label('Tarif PPH')
                ->options(['1%' => '1 %', '2.5%' => '2.5 %'])
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                    $set('jumlah_pph', max($get('harga') * ((float) rtrim($state, '%')) / 100, 0))
                ),

                Forms\Components\TextInput::make('jumlah_pph')->numeric()->nullable()->label('Jumlah PPH'),
                Forms\Components\TextInput::make('kode_billiing_pph')->numeric()->nullable()->label('Kode Billing PPH'),
                Forms\Components\DatePicker::make('tanggal_bayar_pph')->nullable()->label('Tanggal Pembayaran PPH'),
                Forms\Components\TextInput::make('ntpnpph')->numeric()->nullable()->label('NTPN PPH'),
                Forms\Components\TextInput::make('validasi_pph')->numeric()->nullable()->label('Validasi PPH'),
                Forms\Components\DatePicker::make('tanggal_validasi')->nullable()->label('Tanggal Validasi'),

                Forms\Components\Fieldset::make('Dokumen')
                ->schema([
                    Forms\Components\FileUpload::make('up_kode_billing')
                        ->disk('public')
                        ->nullable()
                        ->label('Kode Billing')
                        ->downloadable()
                        ->previewable(false),
            
                    Forms\Components\FileUpload::make('up_bukti_setor_pajak')
                        ->disk('public')
                        ->nullable()
                        ->label('Bukti Setor Pajak')
                        ->downloadable()
                        ->previewable(false),
            
                    Forms\Components\FileUpload::make('up_suket_validasi')
                        ->disk('public')
                        ->nullable()
                        ->label('Suket Validasi')
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
                Tables\Columns\TextColumn::make('no_sertifikat')->sortable()->searchable()->label('No. Sertifikat'),
                Tables\Columns\TextColumn::make('kavling')->sortable()->searchable()->label('Jenis Unit'),
                Tables\Columns\TextColumn::make('nama_konsumen')->sortable()->searchable()->label('Nama Konsumen'),
                Tables\Columns\TextColumn::make('nik')->sortable()->searchable()->label('NIK'),
                Tables\Columns\TextColumn::make('npwp')->sortable()->searchable()->label('NPWP'),
                Tables\Columns\TextColumn::make('alamat')->sortable()->searchable()->label('Alamat'),
                Tables\Columns\TextColumn::make('nop')->sortable()->searchable()->label('NOP'),
                Tables\Columns\TextColumn::make('luas_tanah')->sortable()->searchable()->label('Luas Sertifikat'),
                Tables\Columns\TextColumn::make('harga')->sortable()->searchable()->label('Harga'),
                Tables\Columns\TextColumn::make('npoptkp')->sortable()->searchable()->label('NPOPTKP'),
                Tables\Columns\TextColumn::make('jumlah_bphtb')->sortable()->searchable()->label('Jumlah BPHTB'),
                Tables\Columns\TextColumn::make('tarif_pph')->sortable()->searchable()->label('Tarif PPH'),
                Tables\Columns\TextColumn::make('jumlah_pph')->sortable()->searchable()->label('Jumlah PPH'),
                Tables\Columns\TextColumn::make('kode_billing_pph')->sortable()->searchable()->label('Kode Billing PPH'),
                Tables\Columns\TextColumn::make('tanggal_bayar_pph')->sortable()->searchable()->label('Tanggal Bayar PPH'),
                Tables\Columns\TextColumn::make('ntpnpph')->sortable()->searchable()->label('NTPN PPH'),
                Tables\Columns\TextColumn::make('validasi_pph')->sortable()->searchable()->label('Validasi PPH'),
                Tables\Columns\TextColumn::make('tanggal_validasi')->sortable()->searchable()->label('Tanggal validasi'),

                Tables\Columns\TextColumn::make('up_kode_billing')
                    ->label('Dokumen Kode Billing')
                    ->formatStateUsing(fn ($record) => $record->up_kode_billing
                    ? '<a href="' . Storage::url($record->up_kode_billing) . '" target="_blank">Lihat </a> | 
                    <a href="' . Storage::url($record->up_kode_billing) . '" download>Download</a>' 
                    : 'Tidak Ada Dokumen')
                    ->html()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('up_bukti_setor_pajak')
                    ->label('Dokumen Bukti Setor Pajak')
                    ->formatStateUsing(fn ($record) => $record->up_bukti_setor_pajak
                    ? '<a href="' . Storage::url($record->up_bukti_setor_pajak) . '" target="_blank">Lihat </a> | 
                    <a href="' . Storage::url($record->up_bukti_setor_pajak) . '" download>Download</a>' 
                    : 'Tidak Ada Dokumen')
                    ->html()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('up_suket_validasi')
                    ->label('Dokumen Suket Validasi')
                    ->formatStateUsing(fn ($record) => $record->up_suket_validasi
                    ? '<a href="' . Storage::url($record->up_suket_validasi) . '" target="_blank">Lihat </a> | 
                    <a href="' . Storage::url($record->up_suket_validasi ) . '" download>Download</a>' 
                    : 'Tidak Ada Dokumen')
                    ->html()
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                ->label('Data yang dihapus') 
                ->native(false),

                Filter::make('kavling')
                    ->form([
                        Select::make('kavling')
                            ->options([
                                'standar' => 'Standar',
                                'khusus' => 'Khusus',
                                'hook' => 'Hook',
                                'komersil' => 'Komersil',
                                'tanah_lebih' => 'Tanah Lebih',
                                'kios' => 'Kios',
                            ])
                            ->nullable()
                            ->label('Jenis Unit')
                            ->native(false),
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when(isset($data['kavling']), fn ($q) =>
                            $q->where('kavling', $data['kavling'])
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
                                ->title('Data Validasi Diubah')
                                ->body('Data Validasi telah berhasil disimpan.')),                    
                        DeleteAction::make()
                        ->color('danger')
                        ->label('Hapus')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data Validasi Dihapus')
                                ->body('Data Validasi telah berhasil dihapus.')),
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
                            ->title('Data Validasi')
                            ->body('Data Validasi berhasil dikembalikan.')
                    ),
                    Tables\Actions\ForceDeleteAction::make()
                    ->color('primary')
                    ->label('Hapus Permanen')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data Validasi')
                            ->body('Data Validasi berhasil dihapus secara permanen.')
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
                                ->title('Data Validasi')
                                ->body('Data Validasi berhasil dihapus.'))                        
                                ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),
                
                    BulkAction::make('forceDelete')
                        ->label('Hapus Permanent')
                        ->icon('heroicon-o-x-circle') 
                        ->color('warning')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Data Validasi')
                                ->body('Data Validasi berhasil dihapus secara permanen.'))                        ->requiresConfirmation()
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
                                ->title('Data Validasi')
                                ->body('Data Validasi berhasil dikembalikan.')),
                ]);
    }

    public static function exportData(Collection $records)
    {
        $csvData = "ID, Blok, No. Sertifikat, Jenis Unit, Nama Konsumen, NIK, NPWP, Alamat, NOP, Luas Tanah, Harga, NPOPTKP, Jumlah BPHTB, Tarif PPH, Jumlah PPH, Kode Billing PPH, Tanggal Bayar PPH, NTPN PPH, Validasi PPH, Tanggal Validasi\n";
    
        foreach ($records as $record) {
            $csvData .= "{$record->id}, {$record->siteplan}, {$record->no_sertifikat}, {$record->kavling}, {$record->nama_konsumen}, {$record->nik}, {$record->npwp}, {$record->alamat}, {$record->nop}, {$record->luas_tanah}, {$record->harga}, {$record->npoptkp}, {$record->jumlah_bphtb}, {$record->tarif_pph}, {$record->jumlah_pph}, {$record->kode_billing_pph}, {$record->tanggal_bayar_pph}, {$record->ntpnpph}, {$record->validasi_pph}, {$record->tanggal_validasi}\n";
        }
    
        return response()->streamDownload(fn () => print($csvData), 'dataValidasi.csv');
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
            'index' => Pages\ListFormPajaks::route('/'),
            'create' => Pages\CreateFormPajak::route('/create'),
            'edit' => Pages\EditFormPajak::route('/{record}/edit'),
        ];
    }
}
