<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Picqer\Barcode\BarcodeGeneratorPNG;


class Product extends Model
{
    use HasFactory;

    protected $table = 'products'; // Sesuaikan dengan nama tabel di database

    protected $fillable = [
        'nama',
        'harga',
        'stok',
        'gambar',
        'barcode',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $generator = new BarcodeGeneratorPNG();
            $barcode = rand(1000000000, 9999999999); // Generate kode barcode unik
            $barcodePath = public_path('storage/barcodes');

            if (!file_exists($barcodePath)) {
                mkdir($barcodePath, 0777, true);
            }

            file_put_contents("$barcodePath/{$barcode}.png", $generator->getBarcode($barcode, $generator::TYPE_CODE_128));

            $product->barcode = $barcode;
            $product->barcode_image = "storage/barcodes/{$barcode}.png"; // Simpan path gambar barcode
        });
    }
}

