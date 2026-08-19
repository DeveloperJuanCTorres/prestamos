@extends('layouts.app')

@section('content')
    <!-- Start wrapper-->
    <div id="wrapper" style="min-height: 100vh;">
    
        @include('partials.sidebar')
        @include('partials.topbar')

        <div class="clearfix"></div>
        
        <div class="content-wrapper">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-white mb-0"><i class="fa fa-dashboard me-2"></i> Dashboard Financiero</h4>
                    <span class="badge badge-light p-2"><i class="fa fa-calendar"></i> Año {{ now()->year }}</span>
                </div>

                <!-- 1. TARJETAS KPI PRINCIPALES -->
                <div class="row">
                    <!-- CAPITAL PRESTADO -->
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <div class="card bg-pattern border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white-50 small text-uppercase font-weight-bold">Capital Colocado</span>
                                    <span class="badge badge-primary"><i class="fa fa-bank"></i></span>
                                </div>
                                <h4 class="text-white font-weight-bold mb-2">S/. {{ number_format($total_prestado, 2) }}</h4>
                                <div class="progress my-2" style="height: 5px; background: rgba(255,255,255,0.15);">
                                    <div class="progress-bar bg-info" style="width: {{ min(100, $porcentaje_recuperacion) }}%"></div>
                                </div>
                                <small class="text-white-50"><i class="fa fa-refresh"></i> {{ $porcentaje_recuperacion }}% Capital Recuperado</small>
                            </div>
                        </div>
                    </div>

                    <!-- RECAUDACIÓN TOTAL -->
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <div class="card bg-pattern border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white-50 small text-uppercase font-weight-bold">Total Recaudado</span>
                                    <span class="badge badge-success"><i class="fa fa-money"></i></span>
                                </div>
                                <h4 class="text-white font-weight-bold mb-2">S/. {{ number_format($total_devuelto, 2) }}</h4>
                                <div class="progress my-2" style="height: 5px; background: rgba(255,255,255,0.15);">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                                <small class="text-white-50"><i class="fa fa-check-circle"></i> Cuotas + Liquidaciones</small>
                            </div>
                        </div>
                    </div>

                    <!-- GANANCIA REAL (INTERESES RECAUDADOS) -->
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <div class="card bg-pattern border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white-50 small text-uppercase font-weight-bold">Ganancia Real</span>
                                    <span class="badge badge-warning"><i class="fa fa-line-chart"></i></span>
                                </div>
                                <h4 class="text-white font-weight-bold mb-2">S/. {{ number_format($total_ganancia_real, 2) }}</h4>
                                <div class="progress my-2" style="height: 5px; background: rgba(255,255,255,0.15);">
                                    <div class="progress-bar bg-warning" style="width: 75%"></div>
                                </div>
                                <small class="text-white-50"><i class="fa fa-pie-chart"></i> Intereses efectivos cobrados</small>
                            </div>
                        </div>
                    </div>

                    <!-- CAPITAL PENDIENTE -->
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <div class="card bg-pattern border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-white-50 small text-uppercase font-weight-bold">Capital Pendiente</span>
                                    <span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i></span>
                                </div>
                                <h4 class="text-white font-weight-bold mb-2">S/. {{ number_format($capital_pendiente, 2) }}</h4>
                                <div class="progress my-2" style="height: 5px; background: rgba(255,255,255,0.15);">
                                    <div class="progress-bar bg-danger" style="width: {{ 100 - min(100, $porcentaje_recuperacion) }}%"></div>
                                </div>
                                <small class="text-white-50"><i class="fa fa-user"></i> {{ $clients }} Clientes registrados</small>
                            </div>
                        </div>
                    </div>
                </div>  

                <!-- 2. RESUMEN SECUNDARIO DE PORTAFOLIO -->
                <div class="row mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card card-body p-2 text-center bg-dark text-white border-light">
                            <small class="text-muted">Préstamos Activos</small>
                            <h5 class="mb-0 text-info font-weight-bold">{{ $prestamos_activos_count }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-body p-2 text-center bg-dark text-white border-light">
                            <small class="text-muted">Liquidados</small>
                            <h5 class="mb-0 text-warning font-weight-bold">{{ $prestamos_liquidados_count }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-body p-2 text-center bg-dark text-white border-light">
                            <small class="text-muted">Pagados 100%</small>
                            <h5 class="mb-0 text-success font-weight-bold">{{ $prestamos_pagados_count }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card card-body p-2 text-center bg-dark text-white border-light">
                            <small class="text-muted">En Mora / Vencidos</small>
                            <h5 class="mb-0 text-danger font-weight-bold">{{ $prestamos_vencidos_count }}</h5>
                        </div>
                    </div>
                </div>
            
                <!-- 3. SECCIÓN DE GRÁFICOS -->
                <div class="row">
                    <!-- GRÁFICO PRINCIPAL: EVOLUCIÓN FINANCIERA -->
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-line-chart me-1"></i> Evolución Financiera Mensual ({{ now()->year }})</span>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-1" style="position: relative; height:320px;">
                                    <canvas id="chart-1"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GRÁFICO SECUNDARIO: PORTAFOLIO Y TIPOS DE PAGOS -->
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="fa fa-pie-chart me-1"></i> Estado del Portafolio
                            </div>
                            <div class="card-body">
                                <div class="chart-container-2" style="position: relative; height:200px;">
                                    <canvas id="chart-portfolio"></canvas>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th class="text-right">Monto Colocado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tipos as $tipo)
                                                <tr>
                                                    <td>
                                                        <i class="fa fa-circle text-info mr-2"></i> 
                                                        {{ $tipo->name }}
                                                    </td>
                                                    <td class="text-right font-weight-bold">
                                                        S/. {{ number_format($prestamosPorTipo[$tipo->id] ?? 0, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. TABLA DE CUENTAS POR COBRAR / VENCIDAS -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-clock-o text-danger me-1"></i> Cuotas Pendientes / Vencidas</span>
                                <span class="badge badge-danger">{{ $prestamosVencidos->count() }} cuotas pendientes</span>
                            </div>
                            <div class="table-responsive d-none d-md-block">
                                <table class="table align-items-center table-flush table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Préstamo</th>
                                            <th>Monto Préstamo</th>
                                            <th>Cuota por Cobrar</th>
                                            <th>Vencimiento</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($prestamosVencidos as $prestamo)
                                            @foreach ($prestamo->payments as $cuota)
                                                <tr>
                                                    <td><strong>{{ $prestamo->client->name ?? 'Sin cliente' }}</strong></td>
                                                    <td>#{{ $prestamo->id }}</td>
                                                    <td>S/. {{ number_format($prestamo->amount, 2) }}</td>
                                                    <td class="text-danger font-weight-bold">S/. {{ number_format($cuota->amount, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('loans.show', $prestamo->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-eye"></i> Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted p-4">
                                                    <i class="fa fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                                    ¡Excelente! No hay cuotas vencidas ni pendientes en retraso.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- VISTA RESPONSIVE MÓVIL --}}
                            <div class="d-block d-md-none p-2">
                                @forelse ($prestamosVencidos as $prestamo)
                                    @foreach ($prestamo->payments as $cuota)
                                        <div class="card mb-2 bg-dark border-light p-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong>Cliente:</strong>
                                                <span>{{ $prestamo->client->name ?? 'Sin cliente' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong>Préstamo:</strong>
                                                <span>#{{ $prestamo->id }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1 text-danger">
                                                <strong>Cuota Vencida:</strong>
                                                <strong>S/. {{ number_format($cuota->amount, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <strong>Fecha:</strong>
                                                <span>{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</span>
                                            </div>
                                            <a href="{{ route('loans.show', $prestamo->id) }}" class="btn btn-primary btn-sm w-100">
                                                Ver Detalle
                                            </a>
                                        </div>
                                    @endforeach
                                @empty
                                    <div class="text-center text-muted p-3">No hay cuotas vencidas</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overlay toggle-menu"></div>
            </div>
        </div>

        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        @include('partials.footer')
    </div>

    @push('script')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                // 1. GRÁFICO DONUT DE ESTADO DE PORTAFOLIO
                var ctxPortfolio = document.getElementById("chart-portfolio").getContext('2d');
                var portfolioChart = new Chart(ctxPortfolio, {
                    type: 'doughnut',
                    data: {
                        labels: ["Activos al día", "Liquidados", "Pagados 100%", "En Mora / Vencidos"],
                        datasets: [{
                            data: [
                                {{ $prestamos_activos_count }},
                                {{ $prestamos_liquidados_count }},
                                {{ $prestamos_pagados_count }},
                                {{ $prestamos_vencidos_count }}
                            ],
                            backgroundColor: [
                                "#17a2b8",
                                "#ffc107",
                                "#28a745",
                                "#dc3545"
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutoutPercentage: 55,
                        legend: {
                            display: true,
                            position: "bottom",
                            labels: {
                                fontColor: "#ffffff",
                                boxWidth: 12
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var label = data.labels[tooltipItem.index];
                                    var value = data.datasets[0].data[tooltipItem.index];
                                    return label + ": " + value + " préstamos";
                                }
                            }
                        }
                    }
                });

                // 2. GRÁFICO DE LÍNEAS: EVOLUCIÓN FINANCIERA MENSUAL
                var ctxFinancial = document.getElementById('chart-1').getContext('2d');
                var financialChart = new Chart(ctxFinancial, {
                    type: 'line',
                    data: {
                        labels: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
                        datasets: [
                            {
                                label: 'Capital Otorgado (S/.)',
                                data: {!! json_encode($montosPrestamos) !!},
                                backgroundColor: "rgba(23, 162, 184, 0.2)",
                                borderColor: "#17a2b8",
                                pointRadius: 4,
                                borderWidth: 3,
                                fill: true
                            },
                            {
                                label: 'Total Recaudado (S/.)',
                                data: {!! json_encode($montosPagos) !!},
                                backgroundColor: "rgba(40, 167, 69, 0.2)",
                                borderColor: "#28a745",
                                pointRadius: 4,
                                borderWidth: 3,
                                fill: true
                            },
                            {
                                label: 'Ganancia de Intereses (S/.)',
                                data: {!! json_encode($montosGanancias) !!},
                                backgroundColor: "rgba(255, 193, 7, 0.15)",
                                borderColor: "#ffc107",
                                pointRadius: 4,
                                borderWidth: 3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { fontColor: '#ffffff' }
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: "rgba(0,0,0,0.85)",
                            titleFontColor: '#ffffff',
                            bodyFontColor: '#ffffff',
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                    return label + ": S/. " + Number(tooltipItem.yLabel).toFixed(2);
                                }
                            }
                        },
                        scales: {
                            xAxes: [{
                                ticks: { fontColor: '#ddd' },
                                gridLines: { color: "rgba(221, 221, 221, 0.08)" }
                            }],
                            yAxes: [{
                                ticks: { 
                                    beginAtZero: true, 
                                    fontColor: '#ddd',
                                    callback: function(value) { return 'S/. ' + value; }
                                },
                                gridLines: { color: "rgba(221, 221, 221, 0.08)" }
                            }]
                        }
                    }
                });

            });
        </script>
    @endpush
@endsection
