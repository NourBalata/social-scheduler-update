<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SallaProduct extends Model
{
    protected $fillable = [
        'salla_account_id', 'salla_product_id', 'name', 'description',
        'price', 'currency', 'image_url', 'product_url', 'status', 'sale_price',
    ];

    protected $casts = ['price' => 'float', 'sale_price' => 'float'];

    public function account() { return $this->belongsTo(SallaAccount::class, 'salla_account_id'); }
}