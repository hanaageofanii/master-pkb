<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormDpResource\Pages;
use App\Models\form_dp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
use Filament\Tables\Actions\ForceDeleteAction;
use App\Models\form_kpr;
use App\Models\FormKpr;
use Filament\Tables;
use App\Filament\Resources\AuditResource\RelationManagers;
use App\Models\Audit;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GCVResource;
use App\Models\GCV;
use App\Filament\Resources\KPRStats;




class FormDpResource extends Resource
{
    protected static ?string $model = form_dp::class;

    protected static ?string $title = "Form Input Data Uang Muka";
    protected static ?string $navigationGroup = "Legal";
    protected static ?string $pluralLabel = "Data Uang Muka";
    protected static ?string $navigationLabel = "Uang Muka";
    protected static ?string $pluralModelLabel = 'Daftar Uang Muka';
    protected static ?string $navigationIcon = 'heroicon-o-folder-arrow-down';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Fieldset::make('Data Konsumen')
            ->schema([
                Select::make('siteplan')
                    ->label('Site Plan')
                    ->options(fn () => form_kpr::pluck('siteplan', 'siteplan')) 
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $data = form_kpr::find($state); 
                            if ($data) {
                                $set('nama_konsumen', $data->nama_konsumen);
                                $set('harga', $data->harga);
                                $set('max_kpr', $data->maksimal_kpr);
                            }
                        }
                    }),
        
                TextInput::make('nama_konsumen')
                    ->label('Nama Konsumen')
                    ->dehydrated(),
        
                TextInput::make('harga')
                    ->label('Harga')
                    ->dehydrated()
                    ->numeric(),
        
                TextInput::make('max_kpr')
                    ->label('Maksimal KPR')
                    ->dehydrated()
                    ->numeric(),
            ]),        

            Fieldset::make('Pembayaran')
            ->schema([
                TextInput::make('sbum')
                    ->required()
                    ->label('SBUM')
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $harga = $get('harga') ?? 0;
                        $max_kpr = $get('max_kpr') ?? 0;
                        $sbum = $state ?? 0;

                        $sisa_pembayaran = max(0, $harga - $max_kpr - $sbum);
                        $set('sisa_pembayaran', $sisa_pembayaran);

                        $dp = $get('dp') ?? 0;
                        $set('laba_rugi', $dp - $sisa_pembayaran);
                    }),

                TextInput::make('sisa_pembayaran')
                    ->required()
                    ->numeric()
                    ->label('Sisa Pembayaran'),

                TextInput::make('dp')
                    ->required()
                    ->numeric()
                    ->label('Uang Muka (DP)')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $sisa_pembayaran = $get('sisa_pembayaran') ?? 0;
                        
                        $set('laba_rugi', ($state ?? 0) - $sisa_pembayaran);
                    }),

                TextInput::make('laba_rugi')
                    ->required()
                    ->numeric()
                    ->label('Laba Rugi'),

                DatePicker::make('tanggal_terima_dp')
                    ->required()
                    ->label('Tanggal Terima Uang Muka'),

                Select::make('pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'potong_komisi' => 'Potong Komisi',
                        'promo' => 'Promo',
                    ])
                    ->required()
                    ->label('Pembayaran'),
            ]),


            Fieldset::make('Dokumen')
                ->schema([
                    FileUpload::make('up_kwitansi')->disk('public')->nullable()->label('Kwitansi')
                        ->downloadable()->previewable(false),
                    FileUpload::make('up_pricelist')->disk('public')->nullable()->label('Price List')
                        ->downloadable()->previewable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siteplan')->searchable()->label('Blok'),
                TextColumn::make('nama_konsumen')->searchable()->label('Nama Konsumen'),
                TextColumn::make('harga')->searchable()->label('Harga'),
                TextColumn::make('max_kpr')->searchable()->label('Max KPR'),
                TextColumn::make('sbum')->searchable()->label('SBUM'),
                TextColumn::make('sisa_pembayaran')->searchable()->label('Sisa Pembayaran'),
                TextColumn::make('dp')->searchable()->label('Uang Muka'),
                TextColumn::make('laba_rugi')->searchable()->label('Laba Rugi Uang Muka'),
                TextColumn::make('tanggal_terima_dp')
                    ->searchable()
                    ->label('Tanggal Terima DP')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d F Y')),
                TextColumn::make('pembayaran')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                            'cash' => 'Cash',
                            'potong_komisi' => 'Potong Komisi',
                            'promo' => 'Promo',
                        default => ucfirst($state), 
                    })
                    ->sortable()
                    ->searchable()
                    ->label('Pembayaran'),
                TextColumn::make('up_kwitansi')
                    ->label('Kwitansi')
                    ->formatStateUsing(fn ($record) => $record->up_kwitansi 
                        ? '<a href="' . Storage::url($record->up_kwitansi) . '" target="_blank">Lihat</a> | 
                        <a href="' . Storage::url($record->up_kwitansi) . '" download>Download</a>' 
                        : 'Tidak Ada Dokumen')
                    ->html(),
                TextColumn::make('up_pricelist')
                    ->label('Price List')
                    ->formatStateUsing(fn ($record) => $record->up_pricelist 
                        ? '<a href="' . Storage::url($record->up_pricelist) . '" target="_blank">Lihat</a> | 
                        <a href="' . Storage::url($record->up_pricelist) . '" download>Download</a>' 
                        : 'Tidak Ada Dokumen')
                    ->html(),
            ])
            ->defaultSort('siteplan', 'asc')
            ->headerActions([
                Action::make('count')
                    ->label(fn ($livewire): string => 'Total: ' . $livewire->getFilteredTableQuery()->count())
                    ->disabled(),
            ])
            ->filters([
                TrashedFilter::make()
                ->label('Data yang dihapus') 
                ->native(false),

                Filter::make('pembayaran')
                    ->label('Pembayaran')
                    ->form([
                        Select::make('pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'potong_komisi' => 'Potong Komisi',
                                'promo' => 'Promo',
                            ])
                            ->nullable()
                            ->native(false),
                    ])
                    ->query(fn ($query, $data) =>
                        $query->when(isset($data['pembayaran']), fn ($q) =>
                            $q->where('pembayaran', $data['pembayaran'])
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
                    RestoreAction::make()
                    ->color('info')
                    ->label('Kembalikan Data')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Data KPR')
                            ->body('Data KPR berhasil dikembalikan.')
                    ),
                    ForceDeleteAction::make()
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
                
                    RestoreBulkAction::make()
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
            'index' => Pages\ListFormDps::route('/'),
            'create' => Pages\CreateFormDp::route('/create'),
            'edit' => Pages\EditFormDp::route('/{record}/edit'),
        ];
    }
}
