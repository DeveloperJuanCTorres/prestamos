<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Pagos Agrupados por Préstamo</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
        .loan-header { background: #eaeaea; font-weight: bold; text-align: left; }
        .badge-success { background-color: #28a745; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .badge-danger { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
    </style>
</head>
<body>

<h2>REPORTE DE PAGOS AGRUPADOS POR PRÉSTAMO</h2>
<p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>

<table class="resumen">
    <tr>
        <td>Total Préstamos</td>
        <td>{{ $prestamos->count() }}</td>
        <td>Total Pagado General</td>
        <td>S/ {{ number_format($totalPagadoGeneral, 2) }}</td>
    </tr>
</table>

@foreach($prestamos as $loan)
    @php
        $totalPagadoPrestamo = $loan->payments->where('paid', 1)->sum('amount');
        $saldoPrestamo = $loan->total_to_pay - $totalPagadoPrestamo;
    @endphp

    <table>
        <tr>
            <td class="loan-header" colspan="4">
                Préstamo ID: {{ $loan->id }} |
                Cliente: {{ $loan->client->name }} |
                Monto: S/ {{ number_format($loan->amount, 2) }} |
                Total a Pagar: S/ {{ number_format($loan->total_to_pay, 2) }} |
                Saldo: S/ {{ number_format($saldoPrestamo, 2) }}
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Monto a pagar</th>
            <th>Fecha Pago</th>
            <th>Estado</th>
        </tr>

        @foreach($loan->payments as $i => $payment)
            @php
                // Suma acumulativa hasta este pago
                $pagadoHastaAhora = $loan->payments->where('paid', 1)
                                    ->where('due_date', '<=', $payment->due_date)
                                    ->sum('amount');
                $saldo = $loan->total_to_pay - $pagadoHastaAhora;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>S/ {{ number_format($payment->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</td>
                <td>
                    @if($payment->paid)
                        <span class="badge badge-success">PAGADO</span>
                    @else
                        <span class="badge badge-danger">PENDIENTE</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <br>
@endforeach

</body>
</html>
