<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['loan_id','due_date','amount','paid','status','cuota'];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payment) {
            if ($payment->isDirty('paid') && !$payment->isDirty('status')) {
                $payment->status = $payment->paid ? 'paid' : 'pending';
            } elseif ($payment->isDirty('status') && !$payment->isDirty('paid')) {
                $payment->paid = ($payment->status === 'paid') ? 1 : 0;
            }
        });
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
