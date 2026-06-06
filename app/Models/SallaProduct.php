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

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function discountPercent(): int
    {
        if (!$this->hasDiscount() || !$this->price) return 0;
        return (int) round(($this->price - $this->sale_price) / $this->price * 100);
    }

    /** Shape returned to the frontend modal */
    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'sale_price'  => $this->sale_price,
            'currency'    => $this->currency ?? 'SAR',
            'image'       => $this->image_url,
            'url'         => $this->product_url,
        ];
    }
}