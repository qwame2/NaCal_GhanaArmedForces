<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'delivery_person',
        'delivery_phone',
        'phone',
        'email',
        'address',
        'desc'
    ];
}
