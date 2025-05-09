<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
// use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use App\Models\Team;


class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    // protected static ?string $tenantOwnershipRelationshipName = 'teams';


    protected static ?string $title = "Role";
    protected static ?string $navigationGroup = "Settings";
    protected static ?string $pluralLabel = "Role";
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Role';
    
    protected static ?int $navigationSort = 3;

    protected static ?string $pluralModelLabel = 'Role';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                TextInput::make('name')
                ->minLength(2)
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Nama'),
                Select::make('permissions')
                ->multiple()
                ->relationship('permissions', 'name')
                ->preload(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('Id')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-M-Y')->sortable()
                    ->searchable()
                    ->label('Created at'),
                    // ,

            ])
            ->filters([
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

//     public function teams()
// {
//     return $this->belongsTo(Team::class);
// }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
