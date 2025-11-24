<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_doc',
        'numero_doc',
        'name',
        'address',
        'email',
        'phone'
    ];
}
