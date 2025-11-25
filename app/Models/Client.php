<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tipo_doc',
        'numero_doc',
        'name',
        'address',
        'email',
        'phone'
    ];

    protected $dates = ['deleted_at'];
}
