@extends('layouts.app')

@section('content')
    <!-- Start wrapper-->
    <div id="wrapper" style="min-height: 100vh;">
    
        @include('partials.sidebar')

        @include('partials.topbar')

        <div class="clearfix"></div>
        
        <div class="content-wrapper">
            <div class="container-fluid">

                <!--Start Dashboard Content-->

                <div class="card mt-3">
                    <div class="card-content">
                        <div class="row row-group m-0">
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">{{$total_prestamos}} <span class="float-right"><i class="fa fa-shopping-cart"></i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Préstamos <span class="float-right">+4.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">{{$total_prestado}} <span class="float-right"><i>S/.</i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Prestado <span class="float-right">+1.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">{{$total_devuelto}} <span class="float-right"><i>S/.</i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Devuelto <span class="float-right">+5.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">{{$clients}} <span class="float-right"><i class="zmdi zmdi-accounts-outline"></i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Clientes <span class="float-right">+2.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
            
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-8">
                        <div class="card">
                            <div class="card-header">Préstamos Anual
                                <div class="card-action">
                                    <!-- <div class="dropdown">
                                        <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                                            <i class="icon-options"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="javascript:void();">Action</a>
                                            <a class="dropdown-item" href="javascript:void();">Another action</a>
                                            <a class="dropdown-item" href="javascript:void();">Something else here</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="javascript:void();">Separated link</a>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- <ul class="list-inline">
                                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-white"></i>Crédito</li>
                                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-light"></i>Débito</li>
                                </ul> -->
                                <div class="chart-container-1">
                                    <canvas id="chart-1"></canvas>
                                </div>
                            </div>
                
                            <!-- <div class="row m-0 row-group text-center border-top border-light-3">
                                <div class="col-12 col-lg-4">
                                    <div class="p-3">
                                        <h5 class="mb-0">S/. 45,300.00</h5>
                                        <small class="mb-0">Crédito<span> <i class="fa fa-arrow-up"></i> 2.43%</span></small>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="p-3">
                                    <h5 class="mb-0">S/. 48,950.00</h5>
                                    <small class="mb-0">Débito <span> <i class="fa fa-arrow-up"></i> 12.65%</span></small>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="p-3">
                                    <h5 class="mb-0">560</h5>
                                    <small class="mb-0">Toal réstamos <span> <i class="fa fa-arrow-up"></i> 5.62%</span></small>
                                    </div>
                                </div>
                            </div> -->
                
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-xl-4">
                        <div class="card">
                            <div class="card-header">Tipos de Pagos
                                <div class="card-action">
                                    <!-- <div class="dropdown">
                                        <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                                            <i class="icon-options"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="javascript:void();">Action</a>
                                            <a class="dropdown-item" href="javascript:void();">Another action</a>
                                            <a class="dropdown-item" href="javascript:void();">Something else here</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="javascript:void();">Separated link</a>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-2">
                                <canvas id="chart-2"></canvas>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center">
                                    <tbody>
                                        @foreach($tipos as $tipo)
                                            <tr>
                                                <td>
                                                    <i class="fa fa-circle text-white mr-2"></i> 
                                                    {{ $tipo->name }}
                                                </td>

                                                {{-- Cantidad de préstamos de ese tipo --}}
                                                <td>
                                                    S/. {{ $prestamosPorTipo[$tipo->id] ?? 0 }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Row-->
            
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">Cuentas por cobrar
                                <div class="card-action">
                                    <div class="dropdown">
                                        <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                                            <i class="icon-options"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="javascript:void();">Action</a>
                                            <a class="dropdown-item" href="javascript:void();">Another action</a>
                                            <a class="dropdown-item" href="javascript:void();">Something else here</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="javascript:void();">Separated link</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Préstamo ID</th>
                                            <th>Monto</th>
                                            <th>Por Cobrar</th>
                                            <th>Fecha</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($prestamosVencidos as $prestamo)
                                            @foreach ($prestamo->payments as $cuota)
                                                <tr>
                                                    <td>{{ $prestamo->client->name }}</td>
                                                    <td>#{{ $prestamo->id }}</td>
                                                    <td>S/. {{ number_format($prestamo->amount, 2) }}</td>
                                                    <td>S/. {{ number_format($cuota->amount, 2) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</td>

                                                    <td>
                                                        <a href="{{ route('loans.show', $prestamo->id) }}" class="btn btn-primary btn-sm">
                                                            Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Row-->

                <!--End Dashboard Content-->
            
                <!--start overlay-->
                <div class="overlay toggle-menu"></div>
                <!--end overlay-->
            
            </div>
            <!-- End container-fluid-->
        
        </div>
        <!--End content-wrapper-->

        <!--Start Back To Top Button-->
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        <!--End Back To Top Button-->
        
        
        
        @include('partials.config')
        @include('partials.footer')
    </div>
    <!--End wrapper-->
    

<!-- Index js -->
    @push('script')
        <script src="assets/js/index.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

                var ctx = document.getElementById("chart-2").getContext('2d');

                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($tipos->pluck('name')) !!},  // nombres dinámicos
                        datasets: [{
                            data: {!! json_encode($prestamosPorTipo->values()) !!}, // datos dinámicos
                            backgroundColor: [
                                "#ffffff",
                                "rgba(255, 255, 255, 0.70)",
                                "rgba(255, 255, 255, 0.50)",
                                "rgba(255, 255, 255, 0.20)",
                                "#dddddd", // puedes agregar más colores si hay más tipos
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        maintainAspectRatio: false, // permite que el contenedor defina el tamaño
                        cutoutPercentage: 40,       // grosor del anillo (menos = más grueso)
                        legend: {
                            display: true,          // mostrar leyenda
                            position: "bottom",
                            labels: {
                                fontColor: "#ffffff", // texto de leyenda en blanco
                                boxWidth: 15
                            }
                        },
                        tooltips: {
                            displayColors: false,
                            titleFontColor: "#ffffff",
                            bodyFontColor: "#ffffff",
                            backgroundColor: "rgba(0,0,0,0.7)",
                            xPadding: 10,
                            yPadding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var value = dataset.data[tooltipItem.index];
                                    var label = data.labels[tooltipItem.index];
                                    return label + ": S/ " + value;
                                }
                            }
                        }
                    }
                });


                // chart1

                var ctx = document.getElementById('chart-1').getContext('2d');

                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
                        datasets: [
                            {
                                label: 'Monto total de préstamos',
                                data: {!! json_encode($montosPrestamos) !!},
                                backgroundColor: "rgba(255, 255, 255, 0.25)",
                                borderColor: "#ffffff",
                                pointRadius: 3,
                                borderWidth: 3,
                                fill: true
                            },
                            {
                                label: 'Pagos realizados',
                                data: {!! json_encode($montosPagos) !!},
                                backgroundColor: "rgba(255, 255, 255, 0.1)",
                                borderColor: "rgba(255, 255, 255, 0.7)",
                                pointRadius: 3,
                                borderWidth: 3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: {
                            display: true,
                            labels: { fontColor: '#ffffff' }
                        },
                        tooltips: {
                            displayColors: false,
                            titleFontColor: '#ffffff',
                            bodyFontColor: '#ffffff',
                            backgroundColor: "rgba(0,0,0,0.7)"
                        },
                        scales: {
                            xAxes: [{
                                ticks: { fontColor: '#ddd' },
                                gridLines: { color: "rgba(221, 221, 221, 0.08)" }
                            }],
                            yAxes: [{
                                ticks: { beginAtZero: true, fontColor: '#ddd' },
                                gridLines: { color: "rgba(221, 221, 221, 0.08)" }
                            }]
                        }
                    }
                });

            });
        </script>

    @endpush
@endsection
