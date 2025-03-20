<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AjbResource\Pages;
use App\Filament\Resources\AjbResource\RelationManagers;
use App\Models\Ajb;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AjbResource extends Resource
{
    protected static ?string $model = Ajb::class;

    protected static ?string $title = "AJB";
    protected static ?string $navigationGroup = "Legal";
    protected static ?string $pluralLabel = "AJB";
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel = 'AJB';
    protected static ?string $pluralModelLabel = 'Daftar AJB';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAjbs::route('/'),
            'create' => Pages\CreateAjb::route('/create'),
            'edit' => Pages\EditAjb::route('/{record}/edit'),
        ];
    }
}
