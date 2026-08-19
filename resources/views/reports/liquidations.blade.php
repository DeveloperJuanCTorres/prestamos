<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Liquidaciones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 12px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .summary-card {
            text-align: center;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #27ae60;
            border-radius: 3px;
        }
        .summary-card h3 {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background-color: #2c3e50;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table tbody tr:hover {
            background-color: #e8f4f8;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            color: #27ae60;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        @media print {
            body {
                background-color: white;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 REPORTE DE LIQUIDACIONES</h1>
            <p>Fecha del reporte: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>

        @if ($cantidadLiquidaciones > 0)
            <!-- Resumen -->
            <div class="summary">
                <div class="summary-card">
                    <h3>Total Liquidaciones</h3>
                    <div class="value">{{ $cantidadLiquidaciones }}</div>
                </div>
                <div class="summary-card">
                    <h3>Capital Liquidado</h3>
                    <div class="value">S/. {{ number_format($totalCapital, 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Interés Cobrado</h3>
                    <div class="value">S/. {{ number_format($totalInteres, 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Total Recaudado</h3>
                    <div class="value" style="border-left-color: #e74c3c; color: #e74c3c;">S/. {{ number_format($totalLiquidado, 2) }}</div>
                </div>
            </div>

            <!-- Tabla de liquidaciones -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Préstamo</th>
                        <th>Cliente</th>
                        <th>Fecha Liquidación</th>
                        <th class="text-right">Capital</th>
                        <th class="text-right">Interés</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Cuotas Canceladas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($liquidations as $liquidation)
                        <tr>
                            <td>#{{ $liquidation->id }}</td>
                            <td>#{{ $liquidation->loan_id }}</td>
                            <td>{{ $liquidation->loan->client->name }}</td>
                            <td>{{ $liquidation->liquidation_date->format('d/m/Y H:i') }}</td>
                            <td class="text-right amount">S/. {{ number_format($liquidation->principal_paid, 2) }}</td>
                            <td class="text-right amount">S/. {{ number_format($liquidation->interest_paid, 2) }}</td>
                            <td class="text-right amount">S/. {{ number_format($liquidation->total_paid, 2) }}</td>
                            <td class="text-center">{{ $liquidation->cuota_vigente - 1 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No hay liquidaciones registradas hasta el momento.
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Reporte generado automáticamente por el Sistema de Gestión de Préstamos</p>
            <p>{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
