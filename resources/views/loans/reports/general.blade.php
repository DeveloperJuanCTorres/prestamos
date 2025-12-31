<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte General de Préstamos</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
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
<p>
    <strong>Fecha:</strong> {{ now()->format('d/m/Y') }} <br>
    <strong>Estado:</strong>
    @if($estado === 'pagado') PAGADOS
    @elseif($estado === 'pendiente') PENDIENTES
    @else AMBOS @endif
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
    // ================= SEPARAR Y ORDENAR =================
    $pagadosList = $prestamos->filter(function($loan) {
        return $loan->payments->where('paid', 1)->sum('amount') >= $loan->total_to_pay;
    })->sortBy(fn($loan) => $loan->client->name)->values();

    $pendientesList = $prestamos->filter(function($loan) {
        return $loan->payments->where('paid', 1)->sum('amount') < $loan->total_to_pay;
    })->sortBy(fn($loan) => $loan->client->name)->values();
@endphp

<!-- ================= DETALLE ================= -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Interés</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>

    {{-- ================= PRÉSTAMOS PAGADOS ================= --}}
    @if($estado === 'ambos' || $estado === 'pagado')
        <tr>
            <td colspan="9" class="titulo-seccion">PRÉSTAMOS PAGADOS</td>
        </tr>

        @forelse($pagadosList as $i => $loan)
            @php
                $pagado = $loan->payments->where('paid', 1)->sum('amount');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $loan->client->name }}</td>
                <td>S/ {{ number_format($loan->amount, 2) }}</td>
                <td>{{ $loan->interest_percent }} %</td>
                <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
                <td>S/ {{ number_format($pagado, 2) }}</td>
                <td>S/ 0.00</td>
                <td>{{ $loan->created_at->format('d/m/Y') }}</td>
                <td>PAGADO</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No hay préstamos pagados</td>
            </tr>
        @endforelse
    @endif

    {{-- ================= PRÉSTAMOS PENDIENTES ================= --}}
    @if($estado === 'ambos' || $estado === 'pendiente')
        <tr>
            <td colspan="9" class="titulo-seccion">PRÉSTAMOS PENDIENTES</td>
        </tr>

        @forelse($pendientesList as $i => $loan)
            @php
                $pagado = $loan->payments->where('paid', 1)->sum('amount');
                $saldo = $loan->total_to_pay - $pagado;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $loan->client->name }}</td>
                <td>S/ {{ number_format($loan->amount, 2) }}</td>
                <td>{{ $loan->interest_percent }} %</td>
                <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
                <td>S/ {{ number_format($pagado, 2) }}</td>
                <td>S/ {{ number_format($saldo, 2) }}</td>
                <td>{{ $loan->created_at->format('d/m/Y') }}</td>
                <td>PENDIENTE</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No hay préstamos pendientes</td>
            </tr>
        @endforelse
    @endif

    </tbody>
</table>

</body>
</html>
