<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function productAttributeValue()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute', 'product_id', 'attribute_value_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id');
    }
}
