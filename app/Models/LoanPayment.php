<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id','due_date','amount','paid','cuota'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
