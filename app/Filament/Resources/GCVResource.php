<?php  

namespace App\Filament\Resources;

use App\Filament\Resources\GCVResource\Pages;
use App\Models\GCV;
use App\Models\Audit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AuditResource;

class GCVResource extends Resource
{
    protected static ?string $model = GCV::class;
    protected static ?string $title = "Grand Cikarang Village";
    protected static ?string $navigationGroup = "Legal";
    protected static ?string $pluralLabel = "GCV";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'GCV';
    protected static ?string $pluralModelLabel = 'Data GCV';

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
                    ->required(),

                Forms\Components\Select::make('nama_perusahaan')
                    ->options([
                        'grand_cikarang_village' => 'Grand Cikarang Village',
                        'taman_kertamukti_residence' => 'Taman Kertamukti Residence',
                        'pesona_cengkong_asri_1' => 'Pesona Cengkong Asri 1',
                    ])
                    ->label('Nama Perusahaan')
                    ->required(),

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
                    ->required(),

                    Forms\Components\Select::make('siteplan')
                    ->label('Siteplan')
                    ->options(Audit::pluck('siteplan', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->reactive() 
                    ->afterStateUpdated(function ($state, callable $set) {
                        $auditStatus = Audit::where('id', $state)->value('status');
                        if ($auditStatus === 'akad') {
                            $set('kpr_status', 'akad'); 
                        }
                    }),
                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->required(),

                Forms\Components\TextInput::make('luas_tanah')
                    ->numeric()
                    ->label('Luas Tanah')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'booking' => 'Booking',
                        'indent' => 'Indent',
                        'ready' => 'Ready',
                    ])
                    ->label('Status')
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_booking')
                    ->label('Tanggal Booking')
                    ->required(),

                Forms\Components\TextInput::make('nama_konsumen')
                    ->label('Nama Konsumen')
                    ->required(),

                Forms\Components\TextInput::make('agent')
                    ->label('Agent')
                    ->required(),

                Forms\Components\Select::make('kpr_status')
                    ->options([
                        'sp3k' => 'SP3K',
                        'akad' => 'Akad',
                        'batal' => 'Batal',
                    ])
                    ->label('KPR Status')
                    ->required(),

                Forms\Components\Textarea::make('ket')
                    ->label('Keterangan')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proyek')->label('Proyek'),
                Tables\Columns\TextColumn::make('nama_perusahaan')->label('Nama Perusahaan'),
                Tables\Columns\TextColumn::make('kavling')->label('Kavling'),
                Tables\Columns\TextColumn::make('audit.siteplan')->label('Siteplan'),
                Tables\Columns\TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('luas_tanah')->label('Luas Tanah'),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('tanggal_booking')->date()->label('Tanggal Booking'),
                Tables\Columns\TextColumn::make('nama_konsumen')->label('Nama Konsumen'),
                Tables\Columns\TextColumn::make('agent')->label('Agent'),
                Tables\Columns\TextColumn::make('kpr_status')
                    ->label('KPR Status')
                    ->default(fn ($record) => $record->audits?->status === 'akad' ? 'Akad' : $record->kpr_status),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'booking' => 'Booking',
                        'indent' => 'Indent',
                        'ready' => 'Ready',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGCVS::route('/'),
            'create' => Pages\CreateGCV::route('/create'),
            'edit' => Pages\EditGCV::route('/{record}/edit'),
        ];
    }
}
