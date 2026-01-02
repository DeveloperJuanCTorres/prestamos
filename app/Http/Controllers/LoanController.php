<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Type;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
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
   
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $loans = Loan::query()
            ->when($search, function($query, $search) {
                $query->where('id', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        return view('loans.index', compact('loans'));
    }

    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $loans = Loan::query()
            ->when($search, function ($query, $search) {
                $query->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%"); // opcional: seguir buscando por ID también
            })
            ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('loans.partials.list_table', compact('loans'))->render(),
                'cards' => view('loans.partials.list_cards', compact('loans'))->render(),
                'pagination' => $loans->links()->render()
            ]);
        }

        return view('loans.index', compact('loans'));
    }

     public function create()
    {
        $types = Type::orderBy('name')->get();
        return view('loans.create', compact('types'));
    }

    // API para obtener los límites de un tipo (usada por JS)
    public function typeLimits($id)
    {
        $type = Type::findOrFail($id);
        return response()->json([
            'minimo' => (float)$type->minimo,
            'maximo' => (float)$type->maximo,
            'periodicity_days' => (int)$type->periodicity_days,
            'num_payments' => (int)$type->num_payments,
            'name' => $type->name,
        ]);
    }

    // Guardar préstamo y generar cronograma
    public function store(Request $request)
    {
        // Validar existencia del tipo antes de usar sus límites
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
            'type_id' => 'required|exists:types,id',
            'interest_percent' => 'required|numeric',
        ]);

        $type = Type::findOrFail($request->type_id);

        // validar rango de interés según tipo (server-side)
        $min = (float)$type->minimo;
        $max = (float)$type->maximo;
        $interestPercent = (float)$request->interest_percent;

        if ($interestPercent < $min || $interestPercent > $max) {
            return back()
                ->withInput()
                ->withErrors(['interest_percent' => "El % de interés debe estar entre {$min}% y {$max}% para el tipo {$type->name}."]);
        }

        DB::beginTransaction();
        try {
            $amount = round((float)$request->amount, 2);

            // Cálculo de interés simple
            // $interestAmount = round($amount * ($interestPercent / 100), 2);
            // $totalToPay = round($amount + $interestAmount, 2);

            $numPayments = $type->num_payments;
            $periodDays = $type->periodicity_days;

            // 🔥 Interés mensual (solo si es 30 días)
            if ($periodDays == 30) {
                // interés por cuota
                $interestAmount = round($amount * ($interestPercent / 100) * $numPayments, 2);
            } else {
                // interés simple normal
                $interestAmount = round($amount * ($interestPercent / 100), 2);
            }

            $totalToPay = round($amount + $interestAmount, 2);

            // Crear préstamo
            $loan = Loan::create([
                'client_id' => $request->client_id,
                'type_id' => $request->type_id,
                'amount' => $amount,
                'interest_percent' => $interestPercent,
                'interest_amount' => $interestAmount,
                'total_to_pay' => $totalToPay,
                'num_payments' => $numPayments,
            ]);

            // Calcular cuota base redondeada al décimo superior
            $basePayment = ceil(($totalToPay * 100 / $numPayments) / 10) / 10;

            $payments = [];
            $currentDate = Carbon::now()->addDays($periodDays);
            $accumulated = 0;

            for ($i = 1; $i <= $numPayments; $i++) {
                if ($i < $numPayments) {
                    $amt = $basePayment;
                    $accumulated += $amt;
                } else {
                    // Última cuota ajusta exacto el total
                    $amt = round($totalToPay - $accumulated, 2);
                }

                $payments[] = [
                    'loan_id'    => $loan->id,
                    'due_date'   => $currentDate->toDateString(),
                    'amount'     => $amt,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'cuota'      => $i
                ];

                $currentDate = $currentDate->copy()->addDays($periodDays);
            }

            // Insertar todas las cuotas
            LoanPayment::insert($payments);

            DB::commit();

            return redirect()->route('loans.show', $loan->id)
                ->with('success', 'Préstamo creado y cronograma generado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al crear préstamo: ' . $e->getMessage()]);
        }
    }    

    // Mostrar préstamo y cronograma
    public function show(Loan $loan)
    {
        $loan->load(['client','type','payments']);
        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        if ($loan->hasAnyPaidPayment()) {
            return redirect()->route('loans.index')
                ->with('error', 'Este préstamo no se puede editar porque ya tiene cuotas pagadas.');
        }
        $types = Type::orderBy('name')->get();

        return view('loans.edit', compact('loan','types'));
    }

    public function update(Request $request, Loan $loan)
    {
        if ($loan->hasAnyPaidPayment()) {
            return back()->with('error', 'No se puede modificar un préstamo con cuotas pagadas.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type_id' => 'required|exists:types,id',
            'interest_percent' => 'required|numeric',
        ]);

        $type = Type::findOrFail($request->type_id);

        // validar rango de interés según tipo (server-side)
        $min = (float)$type->minimo;
        $max = (float)$type->maximo;
        $interestPercent = (float)$request->interest_percent;

        if ($interestPercent < $min || $interestPercent > $max) {
            return back()
                ->withInput()
                ->withErrors(['interest_percent' => "El % de interés debe estar entre {$min}% y {$max}% para el tipo {$type->name}."]);
        }

        DB::beginTransaction();

        try {
            $amount = round((float)$request->amount, 2);

            // Cálculo de interés simple
            $interestAmount = round($amount * ($interestPercent / 100), 2);
            $totalToPay = round($amount + $interestAmount, 2);

            $numPayments = (int) $type->num_payments;
            $periodDays  = (int) $type->periodicity_days;

            if ($numPayments <= 0) {
                throw new \Exception('El tipo seleccionado no tiene número de cuotas válido.');
            }

            // ✅ ACTUALIZAR PRÉSTAMO
            $loan->update([
                'type_id'        => $request->type_id,
                'amount'         => $amount,
                'interest_percent'=> $interestPercent,
                'interest_amount'=> $interestAmount,
                'total_to_pay'   => $totalToPay,
                'num_payments'   => $numPayments,
            ]);

            // ✅ ELIMINAR CUOTAS ANTIGUAS
            $loan->payments()->delete();

            // ✅ CALCULAR CUOTAS NUEVAS (MISMA LÓGICA DEL STORE)
            $basePayment = ceil(($totalToPay * 100 / $numPayments) / 10) / 10;

            $payments = [];
            $currentDate = Carbon::now()->addDays($periodDays);
            $accumulated = 0;

            for ($i = 1; $i <= $numPayments; $i++) {
                if ($i < $numPayments) {
                    $amt = $basePayment;
                    $accumulated += $amt;
                } else {
                    $amt = round($totalToPay - $accumulated, 2);
                }

                $payments[] = [
                    'loan_id'    => $loan->id,
                    'due_date'   => $currentDate->toDateString(),
                    'amount'     => $amt,
                    'paid'       => 0,
                    'cuota'      => $i,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ];

                $currentDate = $currentDate->copy()->addDays($periodDays);
            }

            // ✅ INSERTAR NUEVAS CUOTAS
            LoanPayment::insert($payments);

            DB::commit();

            return redirect()->route('loans.show', $loan->id)
                ->with('success', 'Préstamo actualizado y cronograma regenerado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Error al actualizar préstamo: ' . $e->getMessage()]);
        }
    }


    public function pay($id)
    {
        $payment = LoanPayment::findOrFail($id);
        $payment->paid = 1;
        $payment->save();

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id
        ]);
    }

    // public function printSchedule($id)
    // {
    //     $loan = Loan::with('payments')->findOrFail($id);

    //     $pdf = Pdf::loadView('loans.partials.schedule_pdf', compact('loan'));
    //     // return $pdf->download('cronograma_prestamo_'.$loan->id.'.pdf');
    //     return $pdf->stream('cronograma_'.$loan->id.'_'.time().'.pdf');
    // }

    public function printSchedule($id)
    {
        $loan = Loan::with('payments')->findOrFail($id);

        // Forzar nueva instancia del PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('loans.partials.schedule_pdf', compact('loan'));

        return response()->make($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition'=> 'inline; filename="cronograma_'.$loan->id.'_'.time().'.pdf"',
            'Cache-Control'      => 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0',
            'Pragma'             => 'no-cache',
            'Expires'            => '0',
            'Surrogate-Control'  => 'no-store',
        ]);
    }

    public function ticket($id)
    {
        $payment = LoanPayment::with('loan.client')->findOrFail($id);

        $pdf = Pdf::loadView('loans.partials.ticket_pdf', compact('payment'))
                ->setPaper([0, 0, 203, 300]); // Formato ticket 80mm

        return $pdf->stream('ticket_pago_'.$payment->id.'.pdf');
    }

    /**
     * Nuevo endpoint para datos del ticket (JSON)
     * Usado por Web Bluetooth API
     */
    public function ticketData($id)
    {
        $payment = LoanPayment::with('loan.client')->findOrFail($id);
        
        // Calcular saldo pendiente
        $totalPagado = $payment->loan->payments()
                        ->where('paid', 1)
                        ->where('cuota', '<=', $payment->cuota)
                        ->sum('amount');
        $saldoPendiente = $payment->loan->total_to_pay - $totalPagado;
        
        $ticketData = [
            'cliente' => $payment->loan->client->name,
            'documento' => $payment->loan->client->numero_doc,
            'prestamo_id' => $payment->loan_id,
            'cuota' => $payment->cuota,
            'total_cuotas' => $payment->loan->type->num_payments,
            'monto' => number_format($payment->amount, 2),
            'saldo' => number_format($saldoPendiente, 2),
            'fecha' => $payment->updated_at->format('d/m/Y H:i'),
            'payment_id' => $payment->id
        ];

        // Detectar tipo de dispositivo
        $userAgent = request()->header('User-Agent', '');
        $isAndroid = strpos($userAgent, 'Android') !== false;
        $isIOS = strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false;
        $isChrome = strpos($userAgent, 'Chrome') !== false;
        $isEdge = strpos($userAgent, 'Edg') !== false;
        
        $supportsWebBluetooth = !$isIOS && ($isChrome || $isEdge);
        
        return response()->json([
            'ticket_data' => $ticketData,
            'device_info' => [
                'supports_bluetooth' => $supportsWebBluetooth,
                'is_android' => $isAndroid,
                'is_ios' => $isIOS,
                'recommended_action' => $supportsWebBluetooth ? 'bluetooth' : ($isIOS ? 'share' : 'download')
            ],
            'urls' => [
                'pdf_url' => route('payments.ticket', $id),
                'bluetooth_data_url' => route('payments.ticket.data', $id)
            ]
        ]);
    }

    // public function reporteGeneral()
    // {
    //     $prestamos = Loan::with(['client', 'payments'])->get();

    //     $totalPrestado = $prestamos->sum('amount');
    //     $totalPagado = $prestamos->sum(function ($loan) {
    //         return $loan->payments->where('paid', 1)->sum('amount');
    //     });

    //     $totalPorCobrar = $totalPrestado - $totalPagado;

    //     // Conteo de estados
    //     $pagados = 0;
    //     $pendientes = 0;

    //     foreach ($prestamos as $loan) {
    //         $pagado = $loan->payments->where('paid', 1)->sum('amount');
    //         $saldo = $loan->total_to_pay - $pagado;

    //         if ($saldo <= 0) $pagados++;
    //         else $pendientes++;
    //     }

    //     $pdf = Pdf::loadView('loans.reports.general', compact(
    //         'prestamos',
    //         'totalPrestado',
    //         'totalPagado',
    //         'totalPorCobrar',
    //         'pagados',
    //         'pendientes'
    //     ));

    //     return $pdf->download('reporte_general_prestamos'. time() . '.pdf');
    // }

    public function reporteGeneral(Request $request)
    {
        $estado = $request->get('estado', 'ambos'); // pagado | pendiente | ambos

        $prestamos = Loan::with(['client', 'payments'])->get();

        // Filtrar según estado
        if ($estado === 'pagado') {
            $prestamos = $prestamos->filter(function ($loan) {
                return $loan->payments->where('paid', 1)->sum('amount') >= $loan->total_to_pay;
            });
        }

        if ($estado === 'pendiente') {
            $prestamos = $prestamos->filter(function ($loan) {
                return $loan->payments->where('paid', 1)->sum('amount') < $loan->total_to_pay;
            });
        }

        $totalPrestado = $prestamos->sum('amount');

        $totalPagado = $prestamos->sum(function ($loan) {
            return $loan->payments->where('paid', 1)->sum('amount');
        });

        $totalPorCobrar = $totalPrestado - $totalPagado;

        $pagados = 0;
        $pendientes = 0;

        foreach ($prestamos as $loan) {
            $pagado = $loan->payments->where('paid', 1)->sum('amount');
            $saldo = $loan->total_to_pay - $pagado;

            if ($saldo <= 0) $pagados++;
            else $pendientes++;
        }

        $pdf = Pdf::loadView('loans.reports.general', compact(
            'prestamos',
            'totalPrestado',
            'totalPagado',
            'totalPorCobrar',
            'pagados',
            'pendientes',
            'estado'
        ));

        return $pdf->stream(
            'reporte_general_' . $estado . '_' . time() . '.pdf'
        );
    }


    public function reporteClientes()
    {
        $clientes = Client::with(['loans.payments'])->get();

        $totalClientes = $clientes->count();

        $totalPrestado = 0;
        $totalPagado = 0;
        $totalPorCobrar = 0;

        foreach ($clientes as $cliente) {
            foreach ($cliente->loans as $loan) {
                $pagado = $loan->payments->where('paid', 1)->sum('amount');

                $totalPrestado += $loan->amount;
                $totalPagado += $pagado;
                $totalPorCobrar += ($loan->total_to_pay - $pagado);
            }
        }

        $pdf = Pdf::loadView('reports.clientes', compact(
            'clientes',
            'totalClientes',
            'totalPrestado',
            'totalPagado',
            'totalPorCobrar'
        ));

        return $pdf->stream('reporte_clientes.pdf');
    }

    public function reportePrestamos()
    {
        $prestamos = Loan::with(['client', 'payments'])->get();

        $totalPrestado = $prestamos->sum('amount');

        $totalPagado = $prestamos->sum(function ($loan) {
            return $loan->payments->where('paid', 1)->sum('amount');
        });

        $totalPorCobrar = $totalPrestado - $totalPagado;

        $pdf = Pdf::loadView('reports.prestamos', compact(
            'prestamos',
            'totalPrestado',
            'totalPagado',
            'totalPorCobrar'
        ));

        return $pdf->stream('reporte_prestamos.pdf');
    }

    public function reportePagos()
    {
         // Traemos todos los préstamos con sus pagos y cliente
        $prestamos = Loan::with(['client', 'payments' => function($q) {
            $q->orderBy('cuota', 'asc');
        }])->get();

        $totalPagadoGeneral = 0;
        foreach ($prestamos as $loan) {
            $totalPagadoGeneral += $loan->payments->where('paid', 1)->sum('amount');
        }

        $pdf = Pdf::loadView('reports.pagos', compact('prestamos', 'totalPagadoGeneral'));

        return $pdf->stream('reporte_pagos_agrupados.pdf');
    }

    public function cancelar($id)
    {
        // ✅ VALIDACIÓN DE ADMINISTRADOR DESDE .ENV
        $adminEmails = config('app.admin_usernames');

        if (!auth()->check() || !in_array(auth()->user()->email, $adminEmails)) {
            abort(403, 'No autorizado para cancelar pagos');
        }

        // ✅ BUSCAR EL PAGO
        $payment = LoanPayment::findOrFail($id);

        // ✅ CAMBIAR ESTADO A NO PAGADO
        $payment->paid = 0;
        $payment->save();

        // ✅ RESPUESTA
        return redirect()->back()->with('success', 'Pago cancelado correctamente');
    }
}
