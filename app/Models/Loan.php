<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 
        'type_id', 
        'amount', 
        'interest_percent', 
        'total_to_pay',
        'num_payments'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'loan_id');
    }

    public function getEstadoAttribute()
    {
        return $this->payments()->where('paid', 0)->count() === 0 ? 'pagado' : 'pendiente';
    }
}
