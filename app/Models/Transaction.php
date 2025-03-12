<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total',
        'diskon',
        'total_akhir',
        'pembayaran',
        'status',
        'keterangan',
        'total_amount',
    ];



    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'transaction_items', 'transaction_id', 'product_id')
                    ->withPivot('quantity', 'price');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the transaction.
     */

    

}
