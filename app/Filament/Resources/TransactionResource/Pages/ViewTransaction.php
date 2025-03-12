<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getTableQuery()
    {
        return $this->record->items()->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('product_name')->label('Nama Produk'),
            TextColumn::make('quantity')->label('Jumlah'),
            TextColumn::make('price')->label('Harga')->money('idr'),
            TextColumn::make('subtotal')->label('Subtotal')->money('idr'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Add print receipt action to the header
            Actions\Action::make('printReceipt')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('receipt.print', ['transactionId' => $this->record->id]))
                ->openUrlInNewTab()
                ->tooltip('Cetak struk transaksi'),
        ];
    }
}