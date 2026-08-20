<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Liquidation;
use App\Models\Type;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\FlareClient\View;

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
        $status = $request->input('status', 'all'); // Filtro: all, paid, pending
        $sortBy = $request->input('sort', 'id'); // Ordenar por: id, created_at
        $sortOrder = $request->input('order', 'desc'); // desc, asc

        // Construir query base con búsqueda
        $query = Loan::query()->with('client', 'payments');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('loans.id', 'like', "%{$search}%");
            });
        }

        // Aplicar filtro de estado
        if ($status === 'paid') {
            // Préstamos pagados o liquidados:
            $query->where(function ($q) {
                $q->where('liquidated', 1)
                  ->orWhereRaw(
                      'loans.id IN (
                          SELECT loan_id FROM loan_payments
                          WHERE deleted_at IS NULL
                          GROUP BY loan_id
                          HAVING COUNT(*) = SUM(CASE WHEN status = \'paid\' THEN 1 ELSE 0 END)
                      )'
                  );
            });
        } elseif ($status === 'pending') {
            // Préstamos con cuotas pendientes (y que no estén liquidados)
            $query->where('liquidated', 0)
                  ->whereRaw(
                      'loans.id IN (
                          SELECT DISTINCT loan_id FROM loan_payments
                          WHERE status = \'pending\' AND deleted_at IS NULL
                      )'
                  );
        }

        // Aplicar ordenamiento
        $query->orderBy($sortBy === 'created_at' ? 'created_at' : 'id', $sortOrder === 'asc' ? 'asc' : 'desc');

        // Paginar y mantener parámetros de filtro en los enlaces
        $loans = $query->paginate($perPage)->appends(request()->query());

        if ($request->ajax()) {
            return response()->json([
                'table' => view('loans.partials.list_table', compact('loans'))->render(),
                'cards' => view('loans.partials.list_cards', compact('loans'))->render(),
                'pagination' => $loans->links('pagination::bootstrap-4')->render()
            ]);
        }

        return view('loans.index', compact('loans', 'status', 'sortBy', 'sortOrder'));
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



    public function store(Request $request)
    {
        // Validaciones
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'amount'           => 'required|numeric|min:1',
            'type_id'          => 'required|exists:types,id',
            'interest_percent' => 'required|numeric',
        ]);

        $type = Type::findOrFail($request->type_id);

        // Validar rango de interés según tipo
        $min = (float) $type->minimo;
        $max = (float) $type->maximo;
        $interestPercent = (float) $request->interest_percent;

        if ($interestPercent < $min || $interestPercent > $max) {
            return back()
                ->withInput()
                ->withErrors([
                    'interest_percent' => "El % de interés debe estar entre {$min}% y {$max}% para el tipo {$type->name}."
                ]);
        }

        DB::beginTransaction();

        try {
            $amount = round((float) $request->amount, 2);

            $numPayments = (int) $type->num_payments;
            $periodDays  = (int) $type->periodicity_days;

            /*
            |--------------------------------------------------------------------------
            | 🔥 CÁLCULO CORRECTO DEL INTERÉS MENSUAL
            |--------------------------------------------------------------------------
            | Se calcula cuántos días dura el préstamo
            | Luego se convierte a meses (30 días = 1 mes)
            | Se cobra interés mensual por cada mes completo
            */
            $totalDays = $numPayments * $periodDays;
            $months    = ceil($totalDays / 30);

            $interestAmount = round(
                $amount * ($interestPercent / 100) * $months,
                2
            );

            $totalToPay = round($amount + $interestAmount, 2);

            // Crear préstamo
            $loan = Loan::create([
                'client_id'        => $request->client_id,
                'type_id'          => $request->type_id,
                'amount'           => $amount,
                'interest_percent' => $interestPercent,
                'interest_amount'  => $interestAmount,
                'total_to_pay'     => $totalToPay,
                'num_payments'     => $numPayments,
            ]);

            /*
            |--------------------------------------------------------------------------
            | GENERAR CRONOGRAMA DE PAGOS
            |--------------------------------------------------------------------------
            */
            // Cuota base redondeada al décimo superior
            $basePayment = ceil(($totalToPay * 100 / $numPayments) / 10) / 10;

            $payments     = [];
            $currentDate  = Carbon::now()->addDays($periodDays);

            if ($periodDays == 1) {
                while ($currentDate->dayOfWeek == Carbon::SUNDAY) {
                    $currentDate->addDay();
                }
            }

            $accumulated  = 0;

            for ($i = 1; $i <= $numPayments; $i++) {

                if ($i < $numPayments) {
                    $amountPayment = $basePayment;
                    $accumulated += $amountPayment;
                } else {
                    // Última cuota ajusta exacto
                    $amountPayment = round($totalToPay - $accumulated, 2);
                }

                $payments[] = [
                    'loan_id'    => $loan->id,
                    'cuota'      => $i,
                    'due_date'   => $currentDate->toDateString(),
                    'amount'     => $amountPayment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // $currentDate = $currentDate->copy()->addDays($periodDays);
                if ($periodDays == 1) { // Tipo diario
                    do {
                        $currentDate = $currentDate->copy()->addDay();
                    } while ($currentDate->dayOfWeek == Carbon::SUNDAY);
                } else {
                    $currentDate = $currentDate->copy()->addDays($periodDays);
                }
            }

            // Insertar cuotas
            LoanPayment::insert($payments);

            DB::commit();

            return redirect()
                ->route('loans.show', $loan->id)
                ->with('success', 'Préstamo creado y cronograma generado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Error al crear préstamo: ' . $e->getMessage()
                ]);
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
        // ❌ No permitir modificar si hay cuotas pagadas
        if ($loan->hasAnyPaidPayment()) {
            return back()->with('error', 'No se puede modificar un préstamo con cuotas pagadas.');
        }

        // Validaciones
        $request->validate([
            'amount'           => 'required|numeric|min:1',
            'type_id'          => 'required|exists:types,id',
            'interest_percent' => 'required|numeric',
        ]);

        $type = Type::findOrFail($request->type_id);

        // Validar rango de interés según tipo
        $min = (float) $type->minimo;
        $max = (float) $type->maximo;
        $interestPercent = (float) $request->interest_percent;

        if ($interestPercent < $min || $interestPercent > $max) {
            return back()
                ->withInput()
                ->withErrors([
                    'interest_percent' =>
                        "El % de interés debe estar entre {$min}% y {$max}% para el tipo {$type->name}."
                ]);
        }

        DB::beginTransaction();

        try {
            $amount = round((float) $request->amount, 2);

            $numPayments = (int) $type->num_payments;
            $periodDays  = (int) $type->periodicity_days;

            if ($numPayments <= 0 || $periodDays <= 0) {
                throw new \Exception('El tipo seleccionado no tiene configuración válida.');
            }

            /*
            |--------------------------------------------------------------------------
            | 🔥 CÁLCULO CORRECTO DEL INTERÉS MENSUAL
            |--------------------------------------------------------------------------
            */
            $totalDays = $numPayments * $periodDays;
            $months    = ceil($totalDays / 30);

            $interestAmount = round(
                $amount * ($interestPercent / 100) * $months,
                2
            );

            $totalToPay = round($amount + $interestAmount, 2);

            // ✅ ACTUALIZAR PRÉSTAMO SIN CAMBIAR TIMESTAMPS
            $loan->fill([
                'type_id'          => $request->type_id,
                'amount'           => $amount,
                'interest_percent' => $interestPercent,
                'interest_amount'  => $interestAmount,
                'total_to_pay'     => $totalToPay,
                'num_payments'     => $numPayments,
            ]);
            // Desactivar actualización automática de timestamps
            $loan->timestamps = false;
            $loan->save();
            // Reactivar timestamps para futuras operaciones
            $loan->timestamps = true;

            // Obtener fechas programadas originales
            $originalDates = $loan->payments()
                ->orderBy('cuota')
                ->pluck('due_date', 'cuota')
                ->toArray();

            // ✅ ELIMINAR CUOTAS ANTIGUAS
            $loan->payments()->delete();

            /*
            |--------------------------------------------------------------------------
            | GENERAR NUEVO CRONOGRAMA DE PAGOS
            |--------------------------------------------------------------------------
            */
            $basePayment = ceil(($totalToPay * 100 / $numPayments) / 10) / 10;

            $payments    = [];
            
            // Determinar la fecha de inicio para cuotas nuevas (si las hay)
            if (!empty($originalDates)) {
                $lastOriginalDateStr = end($originalDates);
                $currentDate = Carbon::parse($lastOriginalDateStr)->addDays($periodDays);
            } else {
                $currentDate = Carbon::now()->addDays($periodDays);
            }

            // Validar domingos para préstamos diarios (para las cuotas nuevas)
            if ($periodDays == 1) {
                while ($currentDate->dayOfWeek == Carbon::SUNDAY) {
                    $currentDate->addDay();
                }
            }

            $accumulated = 0;

            for ($i = 1; $i <= $numPayments; $i++) {

                if ($i < $numPayments) {
                    $paymentAmount = $basePayment;
                    $accumulated += $paymentAmount;
                } else {
                    // Última cuota ajusta exacto
                    $paymentAmount = round($totalToPay - $accumulated, 2);
                }

                // Usar fecha original si existe
                if (isset($originalDates[$i])) {
                    $dueDateStr = $originalDates[$i] instanceof Carbon ? $originalDates[$i]->toDateString() : (string)$originalDates[$i];
                } else {
                    $dueDateStr = $currentDate->toDateString();
                    // Incrementar fecha para la siguiente cuota nueva
                    if ($periodDays == 1) { // Tipo diario
                        do {
                            $currentDate = $currentDate->copy()->addDay();
                        } while ($currentDate->dayOfWeek == Carbon::SUNDAY);
                    } else {
                        $currentDate = $currentDate->copy()->addDays($periodDays);
                    }
                }

                $payments[] = [
                    'loan_id'    => $loan->id,
                    'cuota'      => $i,
                    'due_date'   => $dueDateStr,
                    'amount'     => $paymentAmount,
                    'paid'       => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insertar nuevas cuotas
            LoanPayment::insert($payments);

            DB::commit();

            return redirect()
                ->route('loans.show', $loan->id)
                ->with('success', 'Préstamo actualizado y cronograma regenerado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Error al actualizar préstamo: ' . $e->getMessage()
                ]);
        }
    }



    public function pay($id)
    {
        $payment = LoanPayment::findOrFail($id);
        $payment->paid = 1;
        $payment->status = 'paid';
        $payment->save();

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id
        ]);
    }

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
        $loan = Loan::findOrFail($payment->loan_id);

        $pdf = Pdf::loadView('loans.partials.ticket_pdf', compact('payment','loan'))
                ->setPaper([0, 0, 203, 335]); // Formato ticket 80mm

        return $pdf->stream('ticket_pago_'.$payment->id.'.pdf');
    }

    public function ticketWhatsapp($id)
    {
        $payment = LoanPayment::with('loan.client')->findOrFail($id);

        // Renderizar la misma vista del PDF
        $html = view('loans.partials.ticket_pdf', compact('payment'))->render();

        // Convertir HTML a texto plano (WhatsApp)
        $texto = trim(
            html_entity_decode(
                strip_tags(
                    preg_replace('/<br\s*\/?>/i', "\n", $html)
                )
            )
        );

        return response()->json([
            'message' => $texto
        ]);
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



    public function reporteGeneral(Request $request)
    {
        $estado = $request->get('estado', 'ambos');

        // TRAER TODO, SIN FILTRAR
        $prestamos = Loan::with(['client', 'payments', 'type'])->get();

        // ================= TOTALES =================
        $totalPrestado = $prestamos->sum('amount');

        $totalPagado = $prestamos->sum(function ($loan) {
            return $loan->payments->where('paid', 1)->sum('amount');
        });

        $totalPorCobrar = $totalPrestado - $totalPagado;

        // ================= CONTADORES =================
        $pagados = $prestamos->filter(function ($loan) {
            return $loan->payments->where('paid', 1)->count() == $loan->num_payments;
        })->count();

        $pendientes = $prestamos->filter(function ($loan) {
            return $loan->payments->where('paid', 1)->count() < $loan->num_payments;
        })->count();

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
        $payment->status = 'pending';
        $payment->save();

        // ✅ RESPUESTA
        return redirect()->back()->with('success', 'Pago cancelado correctamente');
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════════════
     * LIQUIDACIÓN DE DEUDA
     * ═══════════════════════════════════════════════════════════════════════════════
     */

    /**
     * Obtener resumen de liquidación (datos pre-liquidación)
     */
    public function getLiquidationSummary(Loan $loan)
    {
        $loan->load('client', 'type', 'payments');

        if (!$loan->canBeLiquidated()) {
            return response()->json([
                'success' => false,
                'message' => 'Este préstamo no puede ser liquidado'
            ], 422);
        }

        $capitalPaid = $loan->getCapitalPaid();
        $capitalRemaining = $loan->getCapitalRemaining();
        $interestVigente = $loan->interest_per_payment;
        $totalToPay = $capitalRemaining + $interestVigente;

        return response()->json([
            'success' => true,
            'loan_id' => $loan->id,
            'can_liquidate' => true,
            'client_name' => $loan->client->name,
            'total_loan_amount' => (float)$loan->amount,
            'capital_paid' => (float)$capitalPaid,
            'capital_remaining' => (float)$capitalRemaining,
            'interest_percent' => (float)$loan->interest_percent,
            'current_month_interest' => (float)$interestVigente,
            'total_to_pay' => (float)$totalToPay,
            'paid_quotes' => $loan->getPaidPaymentsCount(),
            'pending_quotes' => $loan->getPendingPaymentsCount(),
            'quotes_to_cancel' => $loan->getPendingPaymentsCount(),
            'interest_note' => 'Interés correspondiente a la cuota vigente'
        ]);
    }

    /**
     * Mostrar vista de liquidación (formulario)
     */
    public function showLiquidationForm(Loan $loan)
    {
        if (!$loan->canBeLiquidated()) {
            return redirect()->route('loans.show', $loan->id)
                ->with('error', 'Este préstamo no puede ser liquidado');
        }

        return view('loans.liquidate', compact('loan'));
    }

    /**
     * Ejecutar liquidación de deuda (con doble confirmación)
     */
    public function liquidate(Loan $loan, Request $request)
    {
        // ✅ Validar doble confirmación
        $request->validate([
            'confirm_1' => 'required|accepted',
            'confirm_2' => 'required|accepted'
        ], [
            'confirm_1.required' => 'Se requiere confirmar la liquidación',
            'confirm_1.accepted' => 'Se requiere confirmar la liquidación',
            'confirm_2.required' => 'Se requiere doble confirmación',
            'confirm_2.accepted' => 'Se requiere doble confirmación'
        ]);

        // ✅ Verificar que puede ser liquidado
        if (!$loan->canBeLiquidated()) {
            return back()->with('error', 'Préstamo no puede ser liquidado');
        }

        DB::beginTransaction();
        try {
            $capitalRemaining = $loan->getCapitalRemaining();
            $interestVigente = $loan->interest_per_payment;
            $totalToPay = $capitalRemaining + $interestVigente;

            // 1️⃣ Crear registro de liquidación
            $liquidation = Liquidation::create([
                'loan_id' => $loan->id,
                'user_id' => auth()->id(),
                'liquidation_date' => now(),
                'principal_paid' => $capitalRemaining,
                'interest_paid' => $interestVigente,
                'total_paid' => $totalToPay,
                'cuota_vigente' => $loan->getPaidPaymentsCount() + 1
            ]);

            // 2️⃣ Cancelar cuotas pendientes (status => cancelled)
            $quotesToCancelCount = $loan->getPendingPaymentsCount();
            
            $loan->payments()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // 3️⃣ Marcar préstamo como liquidado
            $loan->update([
                'liquidated' => 1,
                'liquidation_date' => now(),
                'state' => 'liquidated'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deuda liquidada exitosamente',
                'liquidation' => [
                    'id' => $liquidation->id,
                    'loan_id' => $liquidation->loan_id,
                    'principal_paid' => (float)$liquidation->principal_paid,
                    'interest_paid' => (float)$liquidation->interest_paid,
                    'total_paid' => (float)$liquidation->total_paid,
                    'quotes_cancelled' => $quotesToCancelCount,
                    'liquidation_date' => $liquidation->liquidation_date->format('Y-m-d H:i')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en liquidación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error en liquidación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalles de liquidación de un préstamo
     */
    public function getLiquidationDetails(Loan $loan)
    {
        if (!$loan->liquidation) {
            return response()->json([
                'success' => false,
                'message' => 'Este préstamo no ha sido liquidado'
            ], 404);
        }

        $liquidation = $loan->liquidation;

        return response()->json([
            'success' => true,
            'liquidation' => [
                'id' => $liquidation->id,
                'loan_id' => $liquidation->loan_id,
                'principal_paid' => (float)$liquidation->principal_paid,
                'interest_paid' => (float)$liquidation->interest_paid,
                'total_paid' => (float)$liquidation->total_paid,
                'cuota_vigente' => $liquidation->cuota_vigente,
                'liquidation_date' => $liquidation->liquidation_date->format('d/m/Y H:i'),
                'user_name' => $liquidation->user->name ?? 'Sistema'
            ]
        ]);
    }

    /**
     * Reporte de liquidaciones
     */
    public function reporteLiquidaciones()
    {
        $liquidations = Liquidation::with(['loan.client', 'user'])
            ->orderBy('liquidation_date', 'desc')
            ->get();

        $totalLiquidado = $liquidations->sum('total_paid');
        $totalCapital = $liquidations->sum('principal_paid');
        $totalInteres = $liquidations->sum('interest_paid');
        $cantidadLiquidaciones = $liquidations->count();

        $pdf = Pdf::loadView('reports.liquidations', compact(
            'liquidations',
            'totalLiquidado',
            'totalCapital',
            'totalInteres',
            'cantidadLiquidaciones'
        ));

        return $pdf->stream('reporte_liquidaciones_' . time() . '.pdf');
    }

    /**
     * Generar / Imprimir Constancia A4 de Liquidación de Deuda (PDF)
     */
    public function liquidationReceipt(Loan $loan)
    {
        if (!$loan->isLiquidated() || !$loan->liquidation) {
            return redirect()->route('loans.show', $loan->id)
                ->with('error', 'El préstamo aún no ha sido liquidado.');
        }

        $loan->load(['client', 'type', 'payments', 'liquidation.user']);
        $liquidation = $loan->liquidation;

        $pdf = Pdf::loadView('reports.liquidation_receipt', compact('loan', 'liquidation'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('constancia_liquidacion_prestamo_' . $loan->id . '.pdf');
    }
}

