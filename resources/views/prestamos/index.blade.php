@extends('layouts.app')

@section('content')

    <!-- Start wrapper-->
    <div id="wrapper">

        @include('partials.sidebar')

        @include('partials.topbar')

        <div class="clearfix"></div>

        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title m-0">Listado de Préstamos</h5>
                                    <a href="#" class="btn btn-success">Nuevo</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Cliente</th>
                                                <th scope="col">Teléfono</th>
                                                <th scope="col">Préstamo</th>
                                                <th scope="col">%</th>
                                                <th scope="col">Pago</th>
                                                <th scope="col">Saldo</th>
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row">1</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">2</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">3</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">4</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">5</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">6</th>
                                                <td>Mark Otto</td>
                                                <td>978978978</td>
                                                <td>S/. 100.00</td>
                                                <td>20%</td>
                                                <td>S/. 40.00</td>
                                                <td>S/. 80.00</td>
                                                <td>
                                                    <a href="#" class="btn btn-info">Detalle</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Start Back To Top Button-->
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        <!--End Back To Top Button-->

        @include('partials.footer')
        
        @include('partials.config')
    
    </div>
    <!--End wrapper-->


@endsection