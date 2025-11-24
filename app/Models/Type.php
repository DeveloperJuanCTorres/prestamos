<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Type extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'minimo',
        'maximo',
        'periodicity_days',
        'num_payments'
    ];
}
