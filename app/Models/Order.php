<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'products',
        'total_price'
    ];
    protected $casts = [
        'total_price' => 'integer'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
