<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleChecksheetResource\Pages;
use App\Filament\Resources\VehicleChecksheetResource\RelationManagers;
use App\Models\VehicleChecksheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class VehicleChecksheetResource extends Resource
{
    protected static ?string $model = VehicleChecksheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Informasi Kendaraan
                Forms\Components\Section::make('Informasi Kendaraan')
                    ->schema([
                        // Forms\Components\Select::make('asset_id')
                        //     ->relationship('asset', 'name')
                        //     ->label('Nama Aset'),
                        Forms\Components\TextInput::make('reference_number')
                            ->required()
                            ->maxLength(255)
                            ->label('Nomor Referensi')
                            ->readOnly()
                            ->default(function () {
                                return VehicleChecksheetResource::generateReferenceNumber();
                            }),
                        Forms\Components\Select::make('license_plate')
                            ->label('Plat Nomor')
                            ->options(function () {
                                // Ambil data dari AssetAttribute yang terkait dengan CustomAssetAttribute "Plat Nomor"
                                return \App\Models\AssetAttribute::whereHas('customAttribute', function ($query) {
                                    $query->where('name', 'Plat Nomor');
                                })->pluck('attribute_value', 'attribute_value'); // Menggunakan attribute_value sebagai key dan value
                            })
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih Plat Nomor'),
                        Forms\Components\TextInput::make('pic')
                            ->maxLength(255)
                            ->label('PIC (Penanggung Jawab)'),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->label('Lokasi Kendaraan')
                            ->placeholder('Contoh: Depo 1, Workshop, dll.'),
                    ]),

                // Informasi Keberangkatan
                Forms\Components\Section::make('Informasi Keberangkatan')
                    ->schema([
                        Forms\Components\TextInput::make('start_km')
                            ->required()
                            ->numeric()
                            ->label('Kilometer Awal')
                            ->placeholder('Masukkan KM awal'),
                        Forms\Components\DateTimePicker::make('departure_time')
                            ->required()
                            ->label('Waktu Keberangkatan')
                            ->default(now()) // Mengatur nilai default menjadi waktu saat ini
                            ->required(), // Jika field ini wajib diisi
                        Forms\Components\FileUpload::make('departure_photo')
                            ->image()
                            ->resize(50)
                            ->required()
                            ->label('Foto Keberangkatan'),
                        Forms\Components\FileUpload::make('departure_damage_report')
                            ->image()
                            ->resize(50)
                            ->required()
                            ->label('Laporan Kerusakan Saat Keberangkatan'),
                    ]),

                // Informasi Pengembalian
                Forms\Components\Section::make('Informasi Pengembalian')
                    ->schema([
                        Forms\Components\TextInput::make('end_km')
                            ->required()
                            ->numeric()
                            ->label('Kilometer Akhir')
                            ->placeholder('Masukkan KM akhir'),
                        Forms\Components\DateTimePicker::make('return_time')
                            ->required()
                            ->label('Waktu Pengembalian'),
                        Forms\Components\FileUpload::make('return_photo')
                            ->required()
                            ->image()
                            ->resize(50)
                            ->label('Foto Pengembalian'),
                        Forms\Components\FileUpload::make('return_damage_report')
                            ->required()
                            ->image()
                            ->resize(50)
                            ->label('Laporan Kerusakan Saat Pengembalian'),
                    ])
                    ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                // Informasi Tambahan
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('rental_duration')
                            ->numeric()
                            ->label('Durasi Sewa (jam)')
                            ->disabled(), // Set as read-only
                        Forms\Components\TextInput::make('distance_traveled')
                            ->numeric()
                            ->default(0.00)
                            ->label('Jarak Tempuh')
                            ->disabled(), // Set as read-only
                        Forms\Components\Textarea::make('remarks')
                            ->columnSpanFull()
                            ->label('Catatan Tambahan'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pic')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_km')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure_time')
                    ->dateTime()
                    ->sortable(),
                ImageColumn::make('departure_photo')
                    ->checkFileExistence(false),
                ImageColumn::make('departure_damage_report')
                    ->checkFileExistence(false),
                Tables\Columns\TextColumn::make('end_km')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_time')
                    ->dateTime()
                    ->sortable(),
                ImageColumn::make('return_photo')
                    ->checkFileExistence(false),
                ImageColumn::make('return_damage_report')
                    ->checkFileExistence(false),
                Tables\Columns\TextColumn::make('rental_duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('distance_traveled')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByRaw('CAST(SUBSTRING_INDEX(reference_number, "-", -1) AS UNSIGNED) DESC');
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        // Panggil fungsi generateReferenceNumber untuk menghasilkan nomor referensi
        $data['reference_number'] = self::generateReferenceNumber();

        return $data;
    }

    protected static function generateReferenceNumber(): string
    {
        $year = date('Y');

        // Cari record dengan nomor terbesar untuk tahun ini
        $latestRecord = VehicleChecksheet::whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING_INDEX(reference_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($latestRecord) {
            // Ambil nomor urut terakhir dan tambahkan 1
            $lastNumber = (int) Str::afterLast($latestRecord->reference_number, '-');
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Jika belum ada record untuk tahun ini, mulai dari 001
            $newNumber = '001';
        }

        // Format akhir menjadi GA-{tahun}-{nomor urut tiga digit}
        return "GA-{$year}-{$newNumber}";
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
            'index' => Pages\ListVehicleChecksheets::route('/'),
            'create' => Pages\CreateVehicleChecksheet::route('/create'),
            'edit' => Pages\EditVehicleChecksheet::route('/{record}/edit'),
        ];
    }
}
