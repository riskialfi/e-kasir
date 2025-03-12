<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\NumberInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\ImageColumn;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama')
                ->required()
                ->label('Nama Produk'),

            TextInput::make('harga')
                ->numeric()
                ->required()
                ->label('Harga Produk'),

            TextInput::make('stok')
                ->numeric()
                ->required()
                ->label('Stok Produk'),

            FileUpload::make('gambar')->image()->label('Upload Gambar'),
            TextInput::make('barcode')
                ->default(fn() => rand(1000000000, 9999999999))
                ->unique()
                ->label('Barcode'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('gambar')
            ->label('Gambar')
            ->formatStateUsing(fn ($state) => "<img src='" . asset('storage/' . $state) . "' width='80' height='80' style='border-radius: 50%;'>")
            ->html(),
            TextColumn::make('nama')->label('Nama Produk')->sortable()->searchable(),
            TextColumn::make('harga')->label('Harga')->sortable(),
            TextColumn::make('stok')->label('Stok')->sortable(),
            TextColumn::make('barcode_image')
            ->label('Barcode')
            ->formatStateUsing(fn ($state) => "<img src='" . asset($state) . "' width='100'>")
            ->html(), // Harus diaktifkan agar HTML bisa dirender
        // Ukuran gambar barcode
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadBarcode')
                ->label('Barcode')
                ->icon('heroicon-m-arrow-down-tray') // Ikon download yang benar
                ->action(fn ($record) => response()->download(public_path($record->barcode_image)))
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
