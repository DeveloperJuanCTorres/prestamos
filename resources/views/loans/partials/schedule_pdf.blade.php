<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Pago - Préstamo #{{ $loan->id }}</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 25px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 25px;
            font-size: 13px;
        }

        .info p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #343a40;
            color: #fff;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
        }

        td {
            padding: 6px;
            border: 1px solid #000;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
            text-align: center;
            color: #555;
        }
        .badge-success { background-color: #28a745; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .badge-danger { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
    </style>
</head>
<body>

    <div class="header">
        {{-- Logo opcional --}}
        {{-- <img src="{{ public_path('img/logo.png') }}"> --}}
        <div class="title">Cronograma de Pagos</div>
        <div>Préstamo Nº {{ $loan->id }}</div>
    </div>

    <div class="info">
        <p><strong>Cliente:</strong> {{ $loan->client->name }} ({{ $loan->client->numero_doc }})</p>
        <p><strong>Fecha:</strong> {{$loan->created_at->format('d/m/Y') }}</p>
        <p><strong>Tipo de pago:</strong> {{ $loan->type->name }}</p>
        <p><strong>Monto prestado:</strong> S/. {{ number_format($loan->amount, 2) }}</p>
        <p><strong>% Interés:</strong> {{ number_format($loan->interest_percent,2) }}%</p>
        <p><strong>Total a pagar:</strong> S/. {{ number_format($loan->total_to_pay,2) }}</p>
        <p><strong>Cantidad de cuotas:</strong> {{ $loan->num_payments }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha de vencimiento</th>
                <th>Monto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->payments as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->due_date }}</td>
                <td>S/. {{ number_format($p->amount, 2) }}</td>
                <td>
                    @if($p->paid == 1)
                        <span class="badge badge-success">PAGADO</span>
                    @else
                        <span class="badge badge-danger">PENDIENTE</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente – {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
