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
    public function index()
    {
        $loans = Loan::all();
        $clients = Client::count();
        $loan_payments = LoanPayment::all();
        $total_prestamos = $loans->count();
        $hoy = Carbon::now('America/Lima')->toDateString();

        $prestamosVencidos = Loan::whereHas('payments', function ($q) use ($hoy) {
            $q->where('paid', 0)
            ->whereDate('due_date', '<=', $hoy);
        })
        ->with(['payments' => function ($q) use ($hoy) {
            $q->where('paid', 0)
            ->whereDate('due_date', '<=', $hoy);
        }])
        ->get();

        $tipos = Type::all();

        // Cantidad de préstamos por cada tipo
        $prestamosPorTipo = Loan::select('type_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('type_id')
            ->pluck('total_amount', 'type_id');

        $total_prestado = 0;
        $total_devuelto = 0;

        foreach ($loans as $key => $value) {
            $total_prestado = $total_prestado + $value->amount;
        }

        foreach ($loan_payments as $key => $value1) {
            if ($value1->paid == 1) {
                $total_devuelto = $total_devuelto + $value1->amount;
            }            
        }

        $anioActual = Carbon::now()->year;

        // Inicializamos arrays para los 12 meses
        $montosPrestamos = array_fill(1, 12, 0);
        $montosPagos = array_fill(1, 12, 0);

        // 1️⃣ Montos de préstamos por mes
        $prestamos = Loan::whereYear('created_at', $anioActual)->get();
        foreach ($prestamos as $prestamo) {
            $mes = (int) Carbon::parse($prestamo->created_at)->format('n'); // 1-12
            $montosPrestamos[$mes] += $prestamo->amount;
        }

        // 2️⃣ Montos de pagos realizados por mes (según due_date)
        $pagos = LoanPayment::whereYear('due_date', $anioActual)
                    ->where('paid', 1)
                    ->get();

        foreach ($pagos as $pago) {
            $mes = (int) Carbon::parse($pago->due_date)->format('n');
            $montosPagos[$mes] += $pago->amount;
        }

        // Convertimos a valores consecutivos de enero a diciembre
        $montosPrestamos = array_values($montosPrestamos);
        $montosPagos = array_values($montosPagos);

        return view('home',compact('total_prestamos', 'total_prestado', 'total_devuelto', 'clients','prestamosVencidos', 'tipos', 'prestamosPorTipo', 'montosPrestamos', 'montosPagos'));
    }
}
