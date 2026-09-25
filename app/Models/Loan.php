<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'type_id',
        'amount',
        'interest_percent',
        'total_to_pay',
        'num_payments',
        'liquidated',
        'liquidation_date',
        'state'
    ];

    protected $dates = ['deleted_at', 'liquidation_date'];

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

    public function liquidation()
    {
        return $this->hasOne(Liquidation::class);
    }

    public function getEstadoAttribute()
    {
        if ($this->isLiquidated()) {
            return 'liquidado';
        }
        return $this->getPendingPaymentsCount() === 0 ? 'pagado' : 'pendiente';
    }

    public function hasOverduePayments()
    {
        if ($this->isLiquidated() || $this->getPendingPaymentsCount() === 0) {
            return false;
        }
        $today = \Carbon\Carbon::today()->toDateString();
        return $this->payments
            ->where('paid', 0)
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', $today)
            ->count() > 0;
    }

    public function getMaxOverdueDays()
    {
        if (!$this->hasOverduePayments()) {
            return 0;
        }
        $today = \Carbon\Carbon::today();
        $overduePayment = $this->payments
            ->where('paid', 0)
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', $today->toDateString())
            ->sortBy('due_date')
            ->first();

        if (!$overduePayment) return 0;
        $dueDate = \Carbon\Carbon::parse($overduePayment->due_date)->startOfDay();
        return (int)$dueDate->diffInDays($today);
    }

    public function getEstadoDetalleAttribute()
    {
        if ($this->isLiquidated()) {
            return 'liquidado';
        }
        if ($this->getPendingPaymentsCount() === 0) {
            return 'pagado';
        }
        if ($this->hasOverduePayments()) {
            return 'atrasado';
        }
        return 'al_dia';
    }

    public function hasAnyPaidPayment()
    {
        return $this->getPaidPaymentsCount() > 0;
    }

    /**
     * Verificar si el préstamo ya ha sido liquidado
     */
    public function isLiquidated()
    {
        return $this->liquidated === 1 || $this->state === 'liquidated';
    }

    /**
     * Verificar si el préstamo puede ser liquidado
     */
    public function canBeLiquidated()
    {
        return !$this->isLiquidated() && $this->hasPendingPayments();
    }

    /**
     * Obtener la porción de capital por cuota
     */
    public function getCapitalPerPaymentAttribute()
    {
        if ($this->num_payments <= 0) {
            return 0.0;
        }
        return $this->amount / $this->num_payments;
    }

    /**
     * Obtener la porción de interés por cuota
     */
    public function getInterestPerPaymentAttribute()
    {
        if ($this->num_payments <= 0) {
            return 0.0;
        }
        return max(0, $this->total_to_pay - $this->amount) / $this->num_payments;
    }

    /**
     * Obtener el capital pagado hasta ahora (porción de capital de las cuotas pagadas)
     */
    public function getCapitalPaid()
    {
        return $this->capital_per_payment * $this->getPaidPaymentsCount();
    }

    /**
     * Obtener el capital restante
     */
    public function getCapitalRemaining()
    {
        return max(0, $this->amount - $this->getCapitalPaid());
    }

    /**
     * Verificar si hay cuotas pendientes
     */
    public function hasPendingPayments()
    {
        return $this->getPendingPaymentsCount() > 0;
    }

    /**
     * Obtener cantidad de cuotas pendientes
     */
    public function getPendingPaymentsCount()
    {
        return $this->payments()
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->where('status', 'pending')
                          ->orWhereNull('status');
                    })
                    ->where(function ($q2) {
                        $q2->where('paid', 0)
                           ->orWhereNull('paid');
                    })
                    ->count();
    }

    /**
     * Obtener cantidad de cuotas pagadas
     */
    public function getPaidPaymentsCount()
    {
        return $this->payments()
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->where('status', 'paid')
                          ->orWhere(function ($sub) {
                              $sub->where('paid', 1)
                                  ->where(function ($s) {
                                      $s->where('status', '!=', 'cancelled')
                                        ->orWhereNull('status');
                                  });
                          });
                    })
                    ->count();
    }
}
