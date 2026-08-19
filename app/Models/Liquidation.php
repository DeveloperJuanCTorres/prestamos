<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Liquidation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_id',
        'user_id',
        'liquidation_date',
        'principal_paid',
        'interest_paid',
        'total_paid',
        'cuota_vigente'
    ];

    protected $casts = [
        'principal_paid' => 'float',
        'interest_paid' => 'float',
        'total_paid' => 'float',
        'liquidation_date' => 'datetime'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relación: Una liquidación pertenece a un préstamo
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Relación: Una liquidación pertenece a un usuario (quien la realizó)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
