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
        th, td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 9px;}
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
    </style>
</head>
<body>

<h2>REPORTE GENERAL DE PRÉSTAMOS</h2>

<p>
    <strong>Fecha:</strong> {{ now()->format('d/m/Y') }} <br>
    <strong>Estado:</strong>
    {{ strtoupper($estado) }}
</p>

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

@php
    $pagadosList = $prestamos->filter(fn($loan) =>
        $loan->payments->where('paid', 1)->count() == $loan->num_payments
    );

    $pendientesList = $prestamos->filter(fn($loan) =>
        $loan->payments->where('paid', 1)->count() < $loan->num_payments
    );
@endphp

{{-- ================= TABLA PAGADOS ================= --}}
@if($estado === 'ambos' || $estado === 'pagado')
<h3>PAGADOS</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
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
        $sumMonto = $sumTotal = $sumPagado = 0;

        $pagadosList = $pagadosList->sortBy(fn($loan) => $loan->client->name ?? '');
        $pendientesList = $pendientesList->sortBy(fn($loan) => $loan->client->name ?? '');
    @endphp

    @forelse($pagadosList as $loan)
        @php
            $pagado = $loan->payments->where('paid',1)->sum('amount');
            $sumMonto += $loan->amount;
            $sumTotal += $loan->total_to_pay;
            $sumPagado += $pagado;
        @endphp
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $loan->id }}</td>
            <td>{{ $loan->client->name }}</td>
            <td>S/ {{ number_format($loan->amount, 2) }}</td>
            <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
            <td>S/ {{ number_format($pagado, 2) }}</td>
            <td>S/ 0.00</td>
            <td>{{ $loan->created_at->format('d/m/Y') }}</td>
            <td>{{ $loan->type->name }}</td>
            <td>{{ $loan->num_payments }}/{{ $loan->num_payments }}</td>
        </tr>
    @empty
        <tr><td colspan="10">No hay préstamos pagados</td></tr>
    @endforelse

    @if($i > 1)
        <tr style="font-weight:bold">
            <td colspan="3">TOTAL</td>
            <td>S/ {{ number_format($sumMonto, 2) }}</td>
            <td>S/ {{ number_format($sumTotal, 2) }}</td>
            <td>S/ {{ number_format($sumPagado, 2) }}</td>
            <td colspan="4">
                Diferencia: S/ {{ number_format($sumPagado - $sumMonto, 2) }}
            </td>
        </tr>
    @endif
    </tbody>
</table>
@endif

{{-- ================= TABLA PENDIENTES ================= --}}
@if($estado === 'ambos' || $estado === 'pendiente')
<h3>PENDIENTES</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Fecha/V</th>
            <th>Tipo</th>
            <th>Cuota</th>
        </tr>
    </thead>
    <tbody>
    @php
        $i = 1;
        $sumMonto = $sumTotal = $sumPagado = 0;

        $pagadosList = $pagadosList->sortBy(fn($loan) => $loan->client->name ?? '');
        $pendientesList = $pendientesList->sortBy(fn($loan) => $loan->client->name ?? '');
    @endphp

    @forelse($pendientesList as $loan)
        @php
            $pagado = $loan->payments->where('paid',1)->sum('amount');
            $saldo = $loan->total_to_pay - $pagado;
            $sumMonto += $loan->amount;
            $sumTotal += $loan->total_to_pay;
            $sumPagado += $pagado;
        @endphp
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $loan->id }}</td>
            <td>{{ $loan->client->name }}</td>
            <td>S/ {{ number_format($loan->amount, 2) }}</td>
            <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
            <td>S/ {{ number_format($pagado, 2) }}</td>
            <td>S/ {{ number_format($saldo, 2) }}</td>
            <td>{{ $loan->created_at->format('d/m/Y') }}</td>
            <td>{{ $loan->payments->where('paid',0)->first()->due_date }}</td>
            <td>{{ $loan->type->name }}</td>
            <td>{{ $loan->payments->where('paid',1)->count() }}/{{ $loan->num_payments }}</td>
        </tr>
    @empty
        <tr><td colspan="11">No hay préstamos pendientes</td></tr>
    @endforelse

    @if($i > 1)
        <tr style="font-weight:bold">
            <td colspan="3">TOTAL</td>
            <td>S/ {{ number_format($sumMonto, 2) }}</td>
            <td>S/ {{ number_format($sumTotal, 2) }}</td>
            <td>S/ {{ number_format($sumPagado, 2) }}</td>
            <td colspan="5">
                Diferencia: S/ {{ number_format($sumPagado - $sumMonto, 2) }}
            </td>
        </tr>
    @endif
    </tbody>
</table>
@endif

{{-- ================= TABLA COBRANZAS ================= --}}
@if($estado === 'cobranza')
<h3>PENDIENTES</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Cliente</th>
            <th>Capital</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Fecha/V</th>
            <th>Tipo</th>
            <th>Cuota</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
    @php
        $i = 1;
        $sumMonto = $sumTotal = $sumPagado = 0;

        $pagadosList = $pagadosList->sortBy(fn($loan) => $loan->client->name ?? '');
        $pendientesList = $pendientesList->sortBy(fn($loan) => $loan->client->name ?? '');
    @endphp

    @forelse($pendientesList as $loan)
        @php
            // Cuotas pagadas
            $cuotasPagadas = $loan->payments->where('paid', 1)->count();

            // Primera cuota pendiente (ordenada por fecha)
            $cuotaPendiente = $loan->payments
                ->where('paid', 0)
                ->sortBy('due_date')
                ->first();

            $montoCuota = $cuotaPendiente ? $cuotaPendiente->amount : 0;

            // Última cuota pagada (para fecha vencimiento que ya tienes)
            $ultimaPagada = $loan->payments
                ->where('paid', 1)
                ->sortByDesc('due_date')
                ->first();
        @endphp
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $loan->id }}</td>
            <td>{{ $loan->client->name }}</td>
            <td>S/ {{ number_format($loan->amount, 2) }}</td>
            <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
            <td>{{ $loan->created_at->format('d/m/Y') }}</td>
            <td>{{ $loan->payments->where('paid',0)->first()->due_date }}</td>
            <td>{{ $loan->type->name }}</td>
            <td>{{ $cuotasPagadas }}/{{ $loan->num_payments }}</td>
            <td>S/ {{ number_format($montoCuota, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="10">No hay préstamos pendientes</td></tr>
    @endforelse

    @if($i > 1)
        <tr style="font-weight:bold">
            <td colspan="3">TOTAL</td>
            <td>S/ {{ number_format($sumMonto, 2) }}</td>
            <td>S/ {{ number_format($sumTotal, 2) }}</td>
            <td>S/ {{ number_format($sumPagado, 2) }}</td>
            <td colspan="4">
                Diferencia: S/ {{ number_format($sumPagado - $sumMonto, 2) }}
            </td>
        </tr>
    @endif
    </tbody>
</table>
@endif

</body>
</html>
