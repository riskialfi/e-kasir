<?php
namespace App\Filament\Resources\TransactionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class TransactionProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products'; // Sesuaikan dengan relasi di model Transaction

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')->label('Nama Produk'),
                TextColumn::make('pivot.quantity')->label('Jumlah'),
                TextColumn::make('pivot.price')->label('Harga')->money('idr'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('idr')
                    ->formatStateUsing(fn ($record) => $record->pivot->quantity * $record->pivot->price),
            ]);
    }
}
?>
