<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuance extends Model
{
    use HasFactory;

    protected $fillable = [
        'issuance_date',
        'beneficiary',
        'issuance_type'
    ];

    public function items()
    {
        return $this->hasMany(IssuedItem::class);
    }
}
