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

    public function isPaid()
    {
        return $this->status === 'paid' || ($this->status !== 'cancelled' && $this->paid == 1);
    }

    public function getDiasAtrasoAttribute()
    {
        $dueDate = \Carbon\Carbon::parse($this->due_date)->startOfDay();

        if ($this->isPaid()) {
            $paidDate = $this->updated_at ? \Carbon\Carbon::parse($this->updated_at)->startOfDay() : \Carbon\Carbon::today();
            return $paidDate->greaterThan($dueDate) ? (int)$dueDate->diffInDays($paidDate) : 0;
        }

        if ($this->status === 'cancelled') {
            return 0;
        }

        $today = \Carbon\Carbon::today();
        return $today->greaterThan($dueDate) ? (int)$dueDate->diffInDays($today) : 0;
    }

    public function getFechaPagoFormattedAttribute()
    {
        if ($this->isPaid() && $this->updated_at) {
            return \Carbon\Carbon::parse($this->updated_at)->format('d/m/Y');
        }
        return '-';
    }

    public function getFechaVencimientoFormattedAttribute()
    {
        return $this->due_date ? \Carbon\Carbon::parse($this->due_date)->format('d/m/Y') : '-';
    }
}
