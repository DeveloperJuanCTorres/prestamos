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
        .badge-warning { background-color: #ffc107; color: #212529; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .badge-info { background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
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
            <td class="loan-header" colspan="6">
                Préstamo ID: {{ $loan->id }} |
                Cliente: {{ $loan->client->name ?? 'Sin cliente' }} |
                Monto: S/ {{ number_format($loan->amount, 2) }} |
                Total a Pagar: S/ {{ number_format($loan->total_to_pay, 2) }} |
                Saldo: S/ {{ number_format($saldoPrestamo, 2) }} |
                Estado:
                @if($loan->estado_detalle == 'pagado')
                    <span class="badge badge-success">PAGADO</span>
                @elseif($loan->estado_detalle == 'liquidado')
                    <span class="badge badge-warning">LIQUIDADO</span>
                @elseif($loan->estado_detalle == 'atrasado')
                    <span class="badge badge-danger">CON ATRASO</span>
                @else
                    <span class="badge badge-info">AL DÍA</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Monto</th>
            <th>F. Vencimiento</th>
            <th>F. Pago</th>
            <th>Atraso</th>
            <th>Estado</th>
        </tr>

        @foreach($loan->payments as $i => $payment)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>S/ {{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->fecha_vencimiento_formatted }}</td>
                <td>{{ $payment->fecha_pago_formatted }}</td>
                <td>
                    @if($payment->dias_atraso > 0)
                        <span style="color: #dc3545; font-weight: bold;">{{ $payment->dias_atraso }} d.</span>
                    @else
                        <span style="color: #28a745;">0 d.</span>
                    @endif
                </td>
                <td>
                    @if($payment->isPaid())
                        <span class="badge badge-success">PAGADO</span>
                    @elseif($payment->status === 'cancelled')
                        <span class="badge badge-warning">ANULADO</span>
                    @elseif($payment->dias_atraso > 0)
                        <span class="badge badge-danger">VENCIDO</span>
                    @else
                        <span class="badge badge-info">PENDIENTE</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <br>
@endforeach

</body>
</html>
