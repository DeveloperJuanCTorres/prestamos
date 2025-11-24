<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Pago</title>

    <style>
        /* Eliminar márgenes del PDF */
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 14px;
            text-align: center;
            margin: 0;
            padding: 10px;
        }

        .wrapper {
            width: 100%;
            padding: 60px 0 0 0;
            box-sizing: border-box;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        td {
            font-size: 14px;
            padding: 1px;
            text-align: left;
        }

        td:last-child {
            text-align: right;
        }

        p {
            margin: 3px 0;
            padding: 0;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <div class="title">TICKET DE PAGO</div>

    <div class="line"></div>

    <p><strong>Cliente:</strong><br>
        {{ $payment->loan->client->name }}
    </p>

    <p><strong>Documento:</strong><br>
        {{ $payment->loan->client->numero_doc }}
    </p>

    <div class="line"></div>

    <table>
        <tr>
            <td><strong>Préstamo:</strong></td>
            <td>#{{ $payment->loan_id }}</td>
        </tr>

        <tr>
            <td><strong>Cuota:</strong></td>
            <td>{{ $payment->id }}</td>
        </tr>

        <tr>
            <td><strong>Monto:</strong></td>
            <td>S/ {{ number_format($payment->amount, 2) }}</td>
        </tr>

        <tr>
            <td><strong>Fecha pago:</strong></td>
            <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <p>Gracias por su pago</p>
    <p>{{ date('d/m/Y H:i') }}</p>

</div>

</body>
</html>
