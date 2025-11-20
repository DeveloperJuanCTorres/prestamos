@extends('layouts.app')

@section('content')
    <!-- Start wrapper-->
    <div id="wrapper">
    
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
                                    <h5 class="text-white mb-0">150 <span class="float-right"><i class="fa fa-shopping-cart"></i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Préstamos <span class="float-right">+4.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">8500.00 <span class="float-right"><i class="fa fa-usd"></i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Prestado <span class="float-right">+1.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">4300 <span class="float-right"><i class="fa fa-usd"></i></span></h5>
                                    <div class="progress my-3" style="height:3px;">
                                        <div class="progress-bar" style="width:55%"></div>
                                    </div>
                                    <p class="mb-0 text-white small-font">Total Devuelto <span class="float-right">+5.2% <i class="zmdi zmdi-long-arrow-up"></i></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 border-light">
                                <div class="card-body">
                                    <h5 class="text-white mb-0">85 <span class="float-right"><i class="zmdi zmdi-accounts-outline"></i></span></h5>
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
                            <div class="card-body">
                                <ul class="list-inline">
                                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-white"></i>Crédito</li>
                                    <li class="list-inline-item"><i class="fa fa-circle mr-2 text-light"></i>Débito</li>
                                </ul>
                                <div class="chart-container-1">
                                    <canvas id="chart1"></canvas>
                                </div>
                            </div>
                
                            <div class="row m-0 row-group text-center border-top border-light-3">
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
                            </div>
                
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-xl-4">
                        <div class="card">
                            <div class="card-header">Tipos de Pagos
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
                            <div class="card-body">
                                <div class="chart-container-2">
                                <canvas id="chart2"></canvas>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center">
                                    <tbody>
                                        <tr>
                                        <td><i class="fa fa-circle text-white mr-2"></i> Diario</td>
                                        <td>S/. 5,856</td>
                                        <td>+55%</td>
                                        </tr>
                                        <tr>
                                        <td><i class="fa fa-circle text-light-1 mr-2"></i>Semanal</td>
                                        <td>S/. 2,602</td>
                                        <td>+25%</td>
                                        </tr>
                                        <tr>
                                        <td><i class="fa fa-circle text-light-2 mr-2"></i>Mensual</td>
                                        <td>S/. 1,802</td>
                                        <td>+15%</td>
                                        </tr>
                                        <tr>
                                        <td><i class="fa fa-circle text-light-3 mr-2"></i>Otros</td>
                                        <td>S/. 1,105</td>
                                        <td>+5%</td>
                                        </tr>
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
                                            <th>Shipping</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Pedro Lopez</td>
                                            <td>#265</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Enrique Sarmiento</td>
                                            <td>#271</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Jahir Sifuentes</td>
                                            <td>#268</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Yampier Geronimo</td>
                                            <td>#267</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Angel Tenorio</td>
                                            <td>#266</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Ana Gabriel</td>
                                            <td>#270</td>
                                            <td>S/. 200.00</td>
                                            <td>S/.  10.00</td>
                                            <td>20/11/2025</td>
                                            <td>
                                                <div class="progress shadow" style="height: 3px;">
                                                    <div class="progress-bar" role="progressbar" style="width: 90%"></div>
                                                </div>
                                            </td>
                                        </tr>

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
        
        @include('partials.footer')
        
        @include('partials.config')
    
    </div>
    <!--End wrapper-->
@endsection
