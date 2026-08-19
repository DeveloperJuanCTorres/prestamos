<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // public function index()
    // {
    //     $loans = Loan::all();
    //     $clients = Client::count();
    //     $loan_payments = LoanPayment::all();
    //     $total_prestamos = $loans->count();
    //     $hoy = Carbon::now('America/Lima')->toDateString();

    //     $prestamosVencidos = Loan::whereHas('payments', function ($q) use ($hoy) {
    //         $q->where('paid', 0)
    //         ->whereDate('due_date', '<=', $hoy);
    //     })
    //     ->with(['payments' => function ($q) use ($hoy) {
    //         $q->where('paid', 0)
    //         ->whereDate('due_date', '<=', $hoy);
    //     }])
    //     ->get();

    //     $tipos = Type::all();

    //     // Cantidad de préstamos por cada tipo
    //     $prestamosPorTipo = Loan::select('type_id')
    //         ->selectRaw('SUM(amount) as total_amount')
    //         ->groupBy('type_id')
    //         ->pluck('total_amount', 'type_id');

    //     $total_prestado = 0;
    //     $total_devuelto = 0;

    //     foreach ($loans as $key => $value) {
    //         $total_prestado = $total_prestado + $value->amount;
    //     }

    //     foreach ($loan_payments as $key => $value1) {
    //         if ($value1->paid == 1) {
    //             $total_devuelto = $total_devuelto + $value1->amount;
    //         }            
    //     }

    //     $anioActual = Carbon::now()->year;

    //     // Inicializamos arrays para los 12 meses
    //     $montosPrestamos = array_fill(1, 12, 0);
    //     $montosPagos = array_fill(1, 12, 0);

    //     // 1️⃣ Montos de préstamos por mes
    //     $prestamos = Loan::whereYear('created_at', $anioActual)->get();
    //     foreach ($prestamos as $prestamo) {
    //         $mes = (int) Carbon::parse($prestamo->created_at)->format('n'); // 1-12
    //         $montosPrestamos[$mes] += $prestamo->amount;
    //     }

    //     // 2️⃣ Montos de pagos realizados por mes (según due_date)
    //     $pagos = LoanPayment::whereYear('due_date', $anioActual)
    //                 ->where('paid', 1)
    //                 ->get();

    //     foreach ($pagos as $pago) {
    //         $mes = (int) Carbon::parse($pago->due_date)->format('n');
    //         $montosPagos[$mes] += $pago->amount;
    //     }

    //     // Convertimos a valores consecutivos de enero a diciembre
    //     $montosPrestamos = array_values($montosPrestamos);
    //     $montosPagos = array_values($montosPagos);

    //     return view('home',compact('total_prestamos', 'total_prestado', 'total_devuelto', 'clients','prestamosVencidos', 'tipos', 'prestamosPorTipo', 'montosPrestamos', 'montosPagos'));
    // }

    public function index()
    {
        $hoy = Carbon::now('America/Lima')->toDateString();
        $anioActual = Carbon::now()->year;

        // ===============================
        // PRÉSTAMOS Y CLIENTES
        // ===============================
        $loans = Loan::whereNull('deleted_at')
            ->with(['payments', 'liquidation', 'type'])
            ->get();

        $total_prestamos = $loans->count();
        $clients = Client::count();

        // ===============================
        // MÉTRICAS FINANCIERAS REALES
        // ===============================
        // 1. Total Capital Prestado
        $total_prestado = $loans->sum('amount');

        // 2. Interés Total Proyectado
        $total_interes_proyectado = $loans->sum(function ($loan) {
            return max(0, $loan->total_to_pay - $loan->amount);
        });

        // 3. Cobros de Cuotas Normales (pagadas)
        $cuotasPagadas = LoanPayment::where(function ($q) {
                $q->where('status', 'paid')
                  ->orWhere(function ($sub) {
                      $sub->where('paid', 1)
                          ->where(function ($s) {
                              $s->where('status', '!=', 'cancelled')
                                ->orWhereNull('status');
                          });
                  });
            })
            ->whereHas('loan', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->with('loan')
            ->get();

        $total_recaudado_cuotas = $cuotasPagadas->sum('amount');

        // Interés ganado en cuotas normales
        $interes_ganado_cuotas = $cuotasPagadas->sum(function ($pago) {
            if (!$pago->loan || $pago->loan->num_payments <= 0) return 0;
            $totalInterest = max(0, $pago->loan->total_to_pay - $pago->loan->amount);
            return $totalInterest / $pago->loan->num_payments;
        });

        // 4. Cobros por Liquidaciones
        $liquidaciones = \App\Models\Liquidation::whereHas('loan', function ($q) {
            $q->whereNull('deleted_at');
        })->get();

        $total_recaudado_liquidaciones = $liquidaciones->sum('total_paid');
        $interes_ganado_liquidaciones = $liquidaciones->sum('interest_paid');
        $capital_recaudado_liquidaciones = $liquidaciones->sum('principal_paid');

        // Total Recaudado (Cuotas + Liquidaciones)
        $total_devuelto = $total_recaudado_cuotas + $total_recaudado_liquidaciones;

        // Ganancia Real (Intereses Ganados Efectivos)
        $total_ganancia_real = $interes_ganado_cuotas + $interes_ganado_liquidaciones;

        // Capital Recuperado
        $total_capital_recuperado = ($total_recaudado_cuotas - $interes_ganado_cuotas) + $capital_recaudado_liquidaciones;

        // Capital Pendiente por Cobrar
        $capital_pendiente = max(0, $total_prestado - $total_capital_recuperado);

        // Porcentaje de Recuperación del Capital
        $porcentaje_recuperacion = $total_prestado > 0 ? round(($total_capital_recuperado / $total_prestado) * 100, 1) : 0;

        // ===============================
        // ESTADO DEL PORTAFOLIO DE PRÉSTAMOS
        // ===============================
        $prestamos_activos_count = $loans->where('liquidated', 0)->filter(function ($l) {
            return $l->getPendingPaymentsCount() > 0;
        })->count();

        $prestamos_liquidados_count = $loans->where('liquidated', 1)->count();
        
        $prestamos_pagados_count = $loans->where('liquidated', 0)->filter(function ($l) {
            return $l->getPendingPaymentsCount() == 0;
        })->count();

        // Préstamos con cuotas vencidas
        $prestamosVencidos = Loan::whereNull('deleted_at')
            ->where('liquidated', 0)
            ->whereHas('payments', function ($q) use ($hoy) {
                $q->where(function ($sub) {
                    $sub->where('status', 'pending')
                        ->orWhere(function ($s) {
                            $s->where('paid', 0)
                              ->where(function ($s2) {
                                  $s2->where('status', '!=', 'cancelled')
                                     ->orWhereNull('status');
                              });
                        });
                })->whereDate('due_date', '<=', $hoy);
            })
            ->with(['payments' => function ($q) use ($hoy) {
                $q->where(function ($sub) {
                    $sub->where('status', 'pending')
                        ->orWhere(function ($s) {
                            $s->where('paid', 0)
                              ->where(function ($s2) {
                                  $s2->where('status', '!=', 'cancelled')
                                     ->orWhereNull('status');
                              });
                        });
                })->whereDate('due_date', '<=', $hoy);
            }])
            ->get();

        $prestamos_vencidos_count = $prestamosVencidos->count();

        // ===============================
        // TIPOS DE PRÉSTAMO Y DISTRIBUCIÓN
        // ===============================
        $tipos = Type::all();

        $prestamosPorTipo = Loan::whereNull('deleted_at')
            ->select('type_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('type_id')
            ->pluck('total_amount', 'type_id');

        // ===============================
        // GRÁFICO ANUAL (12 MESES)
        // ===============================
        $montosPrestamos = array_fill(1, 12, 0);
        $montosPagos = array_fill(1, 12, 0);
        $montosGanancias = array_fill(1, 12, 0);

        // 1️⃣ Préstamos otorgados por mes
        $prestamosAnio = Loan::whereNull('deleted_at')
            ->whereYear('created_at', $anioActual)
            ->get();

        foreach ($prestamosAnio as $p) {
            $mes = (int) Carbon::parse($p->created_at)->format('n');
            $montosPrestamos[$mes] += $p->amount;
        }

        // 2️⃣ Cobros y Ganancias por mes de Cuotas Normales
        foreach ($cuotasPagadas as $pago) {
            $fechaRef = $pago->updated_at ?? $pago->created_at;
            if ($fechaRef && Carbon::parse($fechaRef)->year == $anioActual) {
                $mes = (int) Carbon::parse($fechaRef)->format('n');
                $montosPagos[$mes] += $pago->amount;

                if ($pago->loan && $pago->loan->num_payments > 0) {
                    $interestQuote = max(0, $pago->loan->total_to_pay - $pago->loan->amount) / $pago->loan->num_payments;
                    $montosGanancias[$mes] += $interestQuote;
                }
            }
        }

        // 3️⃣ Cobros y Ganancias por mes de Liquidaciones
        foreach ($liquidaciones as $liq) {
            if ($liq->created_at && Carbon::parse($liq->created_at)->year == $anioActual) {
                $mes = (int) Carbon::parse($liq->created_at)->format('n');
                $montosPagos[$mes] += $liq->total_paid;
                $montosGanancias[$mes] += $liq->interest_paid;
            }
        }

        $montosPrestamos = array_values($montosPrestamos);
        $montosPagos = array_values($montosPagos);
        $montosGanancias = array_values($montosGanancias);

        return view('home', compact(
            'total_prestamos',
            'total_prestado',
            'total_devuelto',
            'total_ganancia_real',
            'total_interes_proyectado',
            'capital_pendiente',
            'porcentaje_recuperacion',
            'clients',
            'prestamosVencidos',
            'prestamos_activos_count',
            'prestamos_liquidados_count',
            'prestamos_pagados_count',
            'prestamos_vencidos_count',
            'tipos',
            'prestamosPorTipo',
            'montosPrestamos',
            'montosPagos',
            'montosGanancias'
        ));
    }

}
