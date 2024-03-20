<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartTemporary extends Model
{
    use HasFactory;
    protected $table = 'cart_temporary';
    protected $guarded = [];
}
