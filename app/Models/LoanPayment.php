<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['loan_id','due_date','amount','paid','cuota'];

    protected $dates = ['deleted_at'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
