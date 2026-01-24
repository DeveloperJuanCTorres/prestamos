<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte General de Préstamos</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        h3 { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
        .titulo-seccion {
            background: #e9ecef;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>
<body>

<h2>REPORTE GENERAL DE PRÉSTAMOS</h2>
<p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>

<!-- ================= RESUMEN ================= -->
<table class="resumen">
    <tr>
        <td>Total Prestado</td>
        <td>S/ {{ number_format($totalPrestado, 2) }}</td>
        <td>Total Pagado</td>
        <td>S/ {{ number_format($totalPagado, 2) }}</td>
        <td>Por Cobrar</td>
        <td>S/ {{ number_format($totalPorCobrar, 2) }}</td>
    </tr>
    <tr>
        <td>Pagados</td>
        <td>{{ $pagados }}</td>
        <td>Pendientes</td>
        <td>{{ $pendientes }}</td>
        <td colspan="2"></td>
    </tr>
</table>

<!-- ================= TABLA PAGADOS ================= -->
<h3>PAGADOS</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Cuota</th>
        </tr>
    </thead>
    <tbody>
    @php
        $i = 1;
        $sumMonto = 0;
        $sumTotal = 0;
        $sumPagado = 0;
    @endphp

    @foreach($prestamos as $loan)
        @php
            $pagado = $loan->payments->where('paid', 1)->sum('amount');
        @endphp

        @if($pagado >= $loan->total_to_pay)
            @php
                $sumMonto += $loan->amount;
                $sumTotal += $loan->total_to_pay;
                $sumPagado += $pagado;
            @endphp

            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $loan->client->name }}</td>
                <td>S/ {{ number_format($loan->amount, 2) }}</td>
                <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
                <td>S/ {{ number_format($pagado, 2) }}</td>
                <td>S/ 0.00</td>
                <td>{{ $loan->created_at->format('d/m/Y') }}</td>
                <td>{{ $loan->type->name }}</td>
                <td>{{ $loan->payments->where('paid', 1)->count() }}/{{ $loan->num_payments }}</td>
            </tr>
        @endif
    @endforeach

    @if($i === 1)
        <tr>
            <td colspan="9">No hay préstamos pagados</td>
        </tr>
    @else
        <tr style="font-weight:bold; background:#f8f9fa;">
            <td colspan="2">TOTAL</td>
            <td>S/ {{ number_format($sumMonto, 2) }}</td>
            <td>S/ {{ number_format($sumTotal, 2) }}</td>
            <td>S/ {{ number_format($sumPagado, 2) }}</td>
            <td colspan="4">
                Diferencia: S/
                {{ number_format($sumPagado - $sumMonto, 2) }}
            </td>
        </tr>
    @endif
    </tbody>
</table>

<!-- ================= TABLA PENDIENTES ================= -->
<h3>PENDIENTES</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Cuota</th>
        </tr>
    </thead>
    <tbody>
    @php
        $i = 1;
        $sumMonto = 0;
        $sumTotal = 0;
        $sumPagado = 0;
    @endphp

    @foreach($prestamos as $loan)
        @php
            $pagado = $loan->payments->where('paid', 1)->sum('amount');
            $saldo = max(0, $loan->total_to_pay - $pagado);
        @endphp

        @if($pagado < $loan->total_to_pay)
            @php
                $sumMonto += $loan->amount;
                $sumTotal += $loan->total_to_pay;
                $sumPagado += $pagado;
            @endphp

            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $loan->client->name }}</td>
                <td>S/ {{ number_format($loan->amount, 2) }}</td>
                <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
                <td>S/ {{ number_format($pagado, 2) }}</td>
                <td>S/ {{ number_format($saldo, 2) }}</td>
                <td>{{ $loan->created_at->format('d/m/Y') }}</td>
                <td>{{ $loan->type->name }}</td>
                <td>{{ $loan->payments->where('paid', 1)->count() }}/{{ $loan->num_payments }}</td>
            </tr>
        @endif
    @endforeach

    @if($i === 1)
        <tr>
            <td colspan="9">No hay préstamos pendientes</td>
        </tr>
    @else
        <tr style="font-weight:bold; background:#f8f9fa;">
            <td colspan="2">TOTAL</td>
            <td>S/ {{ number_format($sumMonto, 2) }}</td>
            <td>S/ {{ number_format($sumTotal, 2) }}</td>
            <td>S/ {{ number_format($sumPagado, 2) }}</td>
            <td colspan="4">
                Diferencia: S/
                {{ number_format($sumPagado - $sumMonto, 2) }}
            </td>
        </tr>
    @endif
    </tbody>
</table>

</body>
</html>
