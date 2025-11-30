<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Clientes</title>
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
        .cliente-header {
            background: #eaeaea;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>
<body>

<h2>REPORTE GENERAL DE CLIENTES</h2>
<p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>

<table class="resumen">
    <tr>
        <td>Total Clientes</td>
        <td>{{ $totalClientes }}</td>
        <td>Total Prestado</td>
        <td>S/ {{ number_format($totalPrestado, 2) }}</td>
        <td>Total Pagado</td>
        <td>S/ {{ number_format($totalPagado, 2) }}</td>
        <td>Total Por Cobrar</td>
        <td>S/ {{ number_format($totalPorCobrar, 2) }}</td>
    </tr>
</table>

@foreach($clientes as $cliente)

    <table>
        <tr>
            <td class="cliente-header" colspan="7">
                Cliente: {{ $cliente->name }} |
                DNI: {{ $cliente->numero_doc }} |
                Teléfono: {{ $cliente->phone }}
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Monto</th>
            <th>Total a Pagar</th>
            <th>Pagado</th>
            <th>Saldo</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>

        @php
            $i = 1;
            $totalCliente = 0;
            $pagadoCliente = 0;
            $saldoCliente = 0;
        @endphp

        @foreach($cliente->loans as $loan)
        @php
            $pagado = $loan->payments->where('paid', 1)->sum('amount');
            $saldo = $loan->total_to_pay - $pagado;

            $totalCliente += $loan->amount;
            $pagadoCliente += $pagado;
            $saldoCliente += $saldo;
        @endphp
        <tr>
            <td>{{ $i++ }}</td>
            <td>S/ {{ number_format($loan->amount, 2) }}</td>
            <td>S/ {{ number_format($loan->total_to_pay, 2) }}</td>
            <td>S/ {{ number_format($pagado, 2) }}</td>
            <td>S/ {{ number_format($saldo, 2) }}</td>
            <td>{{ $loan->created_at->format('d/m/Y') }}</td>
            <td>{{ $saldo <= 0 ? 'PAGADO' : 'PENDIENTE' }}</td>
        </tr>
        @endforeach

        <tr class="resumen">
            <td colspan="2">Totales Cliente</td>
            <td>S/ {{ number_format($totalCliente, 2) }}</td>
            <td>S/ {{ number_format($pagadoCliente, 2) }}</td>
            <td>S/ {{ number_format($saldoCliente, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <br>

@endforeach

</body>
</html>
