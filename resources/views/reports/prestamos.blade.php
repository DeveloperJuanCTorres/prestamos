<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Préstamos</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
    </style>
</head>
<body>

<h2>REPORTE DE PRÉSTAMOS</h2>
<p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>

<table class="resumen">
    <tr>
        <td>Total Préstamos</td>
        <td>{{ $prestamos->count() }}</td>
        <td>Total Prestado</td>
        <td>S/ {{ number_format($totalPrestado, 2) }}</td>
        <td>Total Pagado</td>
        <td>S/ {{ number_format($totalPagado, 2) }}</td>
        <td>Total por Cobrar</td>
        <td>S/ {{ number_format($totalPorCobrar, 2) }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Total a Pagar</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prestamos as $i => $loan)
        @php
            $pagado = $loan->payments->where('paid', 1)->sum('amount');
            $saldo = $loan->total_to_pay - $pagado;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $loan->client->name }}</td>
            <td>S/ {{ number_format($loan->amount, 2) }}</td>
            <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
            <td>S/ {{ number_format($pagado, 2) }}</td>
            <td>S/ {{ number_format($saldo, 2) }}</td>
            <td>{{ $loan->created_at->format('d/m/Y') }}</td>
            <td>{{ $saldo <= 0 ? 'PAGADO' : 'PENDIENTE' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
