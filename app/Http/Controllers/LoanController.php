<?php

namespace App\Http\Controllers;

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
            ->when($search, function($query, $search) {
                $query->where('id', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('loans.partials.list', compact('loans'))->render(),
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

        // Iniciar transacción
        DB::beginTransaction();
        try {
            $amount = round((float)$request->amount, 2);

            // Cálculo de interés simple (puedes adaptar a interés compuesto si lo deseas)
            $interestAmount = round($amount * ($interestPercent / 100), 2);
            $totalToPay = round($amount + $interestAmount, 2);

            $numPayments = $type->num_payments;
            $periodDays = $type->periodicity_days;

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

            // Generar cronograma: cuotas iguales y ajustar última cuota por redondeo
            $basePayment = floor( ($totalToPay / $numPayments) * 100 ) / 100; // truncar a 2 decimales (down)
            $payments = [];
            $currentDate = Carbon::now()->addDay($periodDays); // empieza desde el siguiente periodo

            // Crear todas menos la última
            $accumulated = 0.00;
            for ($i = 1; $i <= $numPayments; $i++) {
                if ($i < $numPayments) {
                    $amt = $basePayment;
                    $accumulated += $amt;
                } else {
                    // última cuota = total - acumulado (ajuste)
                    $amt = round($totalToPay - $accumulated, 2);
                }

                $payments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $currentDate->toDateString(),
                    'amount' => $amt,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'cuota' => $i
                ];

                $currentDate = $currentDate->copy()->addDays($periodDays);
            }

            // Insertar en bloque
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

    public function printSchedule($id)
    {
        $loan = Loan::with('payments')->findOrFail($id);

        $pdf = Pdf::loadView('loans.partials.schedule_pdf', compact('loan'));
        return $pdf->stream('cronograma_prestamo_'.$loan->id.'.pdf');
    }

    public function ticket($id)
    {
        $payment = LoanPayment::with('loan.client')->findOrFail($id);

        $pdf = Pdf::loadView('loans.partials.ticket_pdf', compact('payment'))
                ->setPaper([0, 0, 203, 300]); // Formato ticket 80mm

        return $pdf->stream('ticket_pago_'.$payment->id.'.pdf');
    }
}
