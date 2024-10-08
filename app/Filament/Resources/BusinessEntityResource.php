<?php

namespace App\Filament\Resources;

use App\Enums\BadgeColor;
use App\Filament\Resources\BusinessEntityResource\Pages;
use App\Filament\Resources\BusinessEntityResource\RelationManagers;
use App\Models\BusinessEntity;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessEntityResource extends Resource
{
    protected static ?string $model = BusinessEntity::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('format')
                    ->required()
                    ->maxLength(255),
                Select::make('color')
                    ->columnSpan(2)
                    ->label('Color')
                    ->allowHtml()
                    ->options(
                        collect(BadgeColor::cases())
                            ->sort(static fn($a, $b) => $a->value <=> $b->value)
                            ->mapWithKeys(static fn($case) => [
                                $case->value => "<span class='flex items-center gap-x-4'>
                            <span class='rounded-full w-4 h-4' style='background:rgb(" . $case->getColor()[600] . ")'></span>
                            <span>" . $case->getLabel() . '</span>
                            </span>',
                            ]),
                    )
                    ->searchable()
                    ->required(),
                FileUpload::make('letterhead')
                    ->image()
                    ->label('Kop Surat')
                    ->disk('public')
                    ->directory('kopsurat')
                    ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->translateLabel('Business Entity')
                    ->searchable()
                    ->badge()
                    ->color(fn($record) => $record->color)
                    ->getStateUsing(fn($record) => $record->name),
                TextColumn::make('format')->translateLabel(),
                TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime(),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBusinessEntities::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Business Entity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Business Entities');
    }
}
