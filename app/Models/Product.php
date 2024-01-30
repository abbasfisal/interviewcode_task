<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property integer $price
 * @property integer $inventory
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'inventory'
    ];
    protected $casts = [
        'name' => 'string',
        'price' => 'integer',
        'inventory' => 'integer'
    ];
}
