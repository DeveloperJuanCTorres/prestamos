<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Liquidación - Préstamo #{{ $loan->id }}</title>
    <style>
        @page {
            margin: 25px;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #2b2b2b;
            font-size: 12px;
            line-height: 1.4;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a365d;
            letter-spacing: 1px;
        }
        .company-sub {
            font-size: 11px;
            color: #4a5568;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 16px;
            color: #2b6cb0;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title p {
            font-size: 11px;
            color: #718096;
            margin: 2px 0 0 0;
        }
        
        .box {
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .box-title {
            font-size: 13px;
            font-weight: bold;
            color: #2d3748;
            border-bottom: 1px solid #cbd5e0;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #4a5568;
            width: 35%;
        }
        
        .highlight-box {
            background-color: #f0fff4;
            border: 1px solid #c6f6d5;
            border-left: 5px solid #38a169;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #276749;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #2d3748;
            color: white;
            padding: 8px 10px;
            font-size: 11px;
            text-align: left;
        }
        .table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .table tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .badge-paid {
            color: #276749;
            font-weight: bold;
        }
        .badge-cancelled {
            color: #718096;
            font-weight: bold;
            text-decoration: line-through;
        }
        
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signature-line {
            border-top: 1px solid #a0aec0;
            width: 200px;
            margin: 0 auto;
            text-align: center;
            padding-top: 5px;
            font-size: 11px;
            color: #4a5568;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #a0aec0;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- CABECERA -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">CREDI-ANRO</div>
                <div class="company-sub">Sistema de Gestión de Préstamos y Créditos</div>
                <div class="company-sub">Comprobante Oficial de Cancelación Anticipada</div>
            </td>
            <td style="width: 40%; text-align: right;" class="doc-title">
                <h2>CONSTANCIA DE LIQUIDACIÓN</h2>
                <p><strong>N° Liquidación:</strong> #LIQ-{{ $liquidation->id }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($liquidation->liquidation_date)->format('d/m/Y h:i A') }}</p>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL CLIENTE Y DEL PRÉSTAMO -->
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 49%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">Información del Cliente</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Nombre:</td>
                            <td><strong>{{ $loan->client->name ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Documento:</td>
                            <td>{{ $loan->client->tipo_doc }} {{ $loan->client->numero_doc }}</td>
                        </tr>
                        <tr>
                            <td class="label">Teléfono:</td>
                            <td>{{ $loan->client->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Dirección:</td>
                            <td>{{ $loan->client->address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%; vertical-align: top;">
                <div class="box">
                    <div class="box-title">Datos del Préstamo</div>
                    <table class="info-table">
                        <tr>
                            <td class="label">Préstamo N°:</td>
                            <td><strong>#{{ $loan->id }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Monto Otorgado:</td>
                            <td>S/. {{ number_format($loan->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Modalidad:</td>
                            <td>{{ $loan->type->name ?? 'N/A' }} ({{ $loan->num_payments }} cuotas)</td>
                        </tr>
                        <tr>
                            <td class="label">Estado Final:</td>
                            <td><strong style="color: #276749;">LIQUIDADO / CANCELADO</strong></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- RESUMEN FINANCIERO DE LIQUIDACIÓN -->
    <div class="highlight-box">
        <div class="box-title" style="color: #276749; border-color: #c6f6d5;">Desglose de Liquidación de Deuda</div>
        <table class="info-table" style="font-size: 13px;">
            <tr>
                <td style="width: 50%;">Cuotas Pagadas Previamente:</td>
                <td style="text-align: right;"><strong>{{ $liquidation->cuota_vigente - 1 }} cuotas</strong></td>
            </tr>
            <tr>
                <td>Capital Restante Cancelado:</td>
                <td style="text-align: right;">S/. {{ number_format($liquidation->principal_paid, 2) }}</td>
            </tr>
            <tr>
                <td>Interés de Cuota Vigente Cobrado:</td>
                <td style="text-align: right;">S/. {{ number_format($liquidation->interest_paid, 2) }}</td>
            </tr>
            <tr style="border-top: 2px solid #9ae6b4;">
                <td style="font-size: 15px; font-weight: bold; color: #22543d; padding-top: 8px;">TOTAL PAGADO PARA LIQUIDAR:</td>
                <td style="text-align: right; padding-top: 8px;" class="total-amount">S/. {{ number_format($liquidation->total_paid, 2) }}</td>
            </tr>
        </table>
        <p style="font-size: 10.5px; color: #276749; margin-top: 8px; font-style: italic;">
            * Nota: Con este pago se exoneran totalmente los intereses de las cuotas restantes y la deuda queda 100% saldada.
        </p>
    </div>

    <!-- CRONOGRAMA DE CUOTAS Y ESTADO FINAL -->
    <div style="font-weight: bold; font-size: 12px; color: #2d3748; margin-top: 10px;">Detalle del Cronograma de Cuotas:</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 10%;"># Cuota</th>
                <th style="width: 25%;">Vencimiento</th>
                <th style="width: 25%; text-align: right;">Monto Programado</th>
                <th style="width: 40%; text-align: right;">Estado Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->payments as $p)
                <tr>
                    <td>Cuota {{ $p->cuota }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->due_date)->format('d/m/Y') }}</td>
                    <td style="text-align: right;">S/. {{ number_format($p->amount, 2) }}</td>
                    <td style="text-align: right;">
                        @if($p->status === 'paid' || ($p->paid == 1 && $p->status !== 'cancelled'))
                            <span class="badge-paid">✓ PAGADO</span>
                        @elseif($p->status === 'cancelled')
                            <span class="badge-cancelled">EXONERADO / ANULADO</span>
                        @else
                            <span class="badge-cancelled">EXONERADO</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FIRMAS -->
    <table class="signatures">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div class="signature-line">
                    <strong>{{ $loan->client->name ?? 'Cliente' }}</strong><br>
                    Firma del Cliente
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center;">
                <div class="signature-line">
                    <strong>{{ $liquidation->user->name ?? auth()->user()->name ?? 'Cajero / Asesor' }}</strong><br>
                    Cajero / Asesor Autorizado
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Este documento es un comprobante válido de cancelación anticipada de préstamo expedido por CREDI-ANRO.<br>
        Impreso el {{ now()->format('d/m/Y h:i A') }}
    </div>

</body>
</html>
