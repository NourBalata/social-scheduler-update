<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SallaAccount extends Model
{
    protected $fillable = [
        'user_id', 'salla_merchant_id', 'store_name', 'store_email',
        'store_avatar', 'store_url', 'access_token', 'refresh_token',
        'token_expires_at', 'auto_post_enabled',
    ];

    protected $casts = [
        'token_expires_at'  => 'datetime',
        'auto_post_enabled' => 'boolean',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function products() { return $this->hasMany(SallaProduct::class); }
}