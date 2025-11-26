@extends('layouts.app')

@section('content')

<div id="wrapper" style="min-height: 100vh;">

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
                                <h5 class="card-title m-0">Listado de Tipos de pagos</h5>
                                <a href="#" class="btn btn-success" id="btnCreate">Nuevo</a>
                            </div>

                            <div class="d-flex mb-3">
                                <input type="text" id="search" class="form-control w-25 mr-2" placeholder="Buscar...">
                                <select id="perPage" class="form-control w-25">
                                    <option value="5">5 por página</option>
                                    <option value="10" selected>10 por página</option>
                                    <option value="25">25 por página</option>
                                    <option value="50">50 por página</option>
                                </select>
                            </div>

                            <div class="table-responsive d-none d-md-block" id="types-container">
                                <table class="table table-striped table-bordered w-100" style="width: 100% !important;">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%;">ID</th>
                                            <th style="width: 20%;">Tipo.</th>
                                            <th style="width: 15%;">% MIN</th>
                                            <th style="width: 15%;">% MAX</th>
                                            <th style="width: 15%;">Periodo</th>
                                            <th style="width: 15%;"># Pagos</th>
                                            <th style="width: 10%;">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('types.partials.list_table')
                                    </tbody>
                                </table>
                            </div>

                            <!-- CARDS MÓVIL -->
                            <div class="d-block d-md-none" id="types-cards-container">
                                @include('types.partials.list_cards')
                            </div>

                            <!-- Contenedor de paginación -->
                            <div class="d-flex justify-content-center mt-2">
                                <div class="pagination-sm">
                                    {{ $types->links('pagination::bootstrap-4') }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i></a>

    @include('partials.footer')
    @include('partials.config')
    @include('types.create')
    @include('types.edit')

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // ===============================
    // Función para cargar clientes
    // ===============================
    function loadTypes(page = 1) {
        let search = $("#search").val();
        let perPage = $("#perPage").val();

        $.ajax({
            url: "{{ route('types.list') }}",
            type: "GET",
            data: { search: search, perPage: perPage, page: page },
            dataType: "json",
            success: function(data) {
                // Actualizar tabla y paginación
                $("#types-container tbody").html(data.table);
                $("#types-cards-container").html(data.cards);
                $("#pagination-container").html(data.pagination);
            }
        });
    }

    // ===============================
    // Buscar al escribir
    // ===============================
    $("#search").on("keyup", function() { loadTypes(); });

    // ===============================
    // Cambiar cantidad de registros
    // ===============================
    $("#perPage").on("change", function() { loadTypes(); });

    // ===============================
    // Paginación
    // ===============================
    $(document).on("click", "#pagination-container a", function(e){
        e.preventDefault();
        let page = $(this).attr("href").split('page=')[1];
        loadTipes(page);
    });

    // ===============================
    // Crear cliente
    // ===============================
    $(document).on("click", "#btnCreate", function(){
        $('#createType').modal('show');
    });

    $(document).on("click", ".registrar", function(e){
        e.preventDefault();
        let formData = new FormData($("#formAgregar")[0]);

        if ($('#name').val() == '') {
            $('#name').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo Tipo es requerido",
                icon: "info"
            });       
            return false;     
        }
        if ($('#minimo').val() == '') {
            $('#minimo').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo % Mínimo es requerido",
                icon: "info"
            });    
            return false;          
        }
        if ($('#maximo').val() == '') {
            $('#maximo').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo % Máximo es requerido",
                icon: "info"
            });     
            return false;         
        }
        if ($('#periodicity_days').val() == '') {
            $('#periodicity_days').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo Periodo es requerido",
                icon: "info"
            });  
            return false;            
        }
        if ($('#num_payments').val() == '') {
            $('#num_payments').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo # Pagos es requerido",
                icon: "info"
            });  
            return false;            
        }

        $.ajax({
            url: "{{ route('types.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response){
                if(response.status){
                    $("#createType").modal("hide");
                    $("#formAgregar")[0].reset();
                    loadTypes();
                    Swal.fire({icon: "success", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                } else {
                    Swal.fire({icon: "error", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                }
            },
            error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
        });
    });

    // ===============================
    // Editar cliente
    // ===============================
    $(document).on("click", ".type-edit", function(e){
        e.preventDefault();
        let id = $(this).data("id");

        $.ajax({
            url: "{{ route('types.edit') }}",
            type: "POST",
            data: {id: id, _token: "{{ csrf_token() }}"},
            success: function(response){
                if(response.status){
                    let c = response.type;
                    $('#edit_id').val(c.id);
                    $('#edit_name').val(c.name);
                    $('#edit_minimo').val(c.minimo);
                    $('#edit_maximo').val(c.maximo);
                    $('#edit_periodo').val(c.periodicity_days);
                    $('#edit_num_payments').val(c.num_payments);
                    $('#editType').modal('show');
                } else {
                    Swal.fire({icon: "error", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                }
            },
            error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
        });
    });

    $(document).on("submit", "#formEditType", function(e){
        e.preventDefault();
        let formData = new FormData(this);

        if ($('#edit_name').val() == '') {
            $('#edit_name').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo Tipo es requerido",
                icon: "info"
            });       
            return false;     
        }
        if ($('#edit_minimo').val() == '') {
            $('#edit_minimo').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo % Mínimo es requerido",
                icon: "info"
            });    
            return false;          
        }
        if ($('#edit_maximo').val() == '') {
            $('#edit_maximo').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo Máximo es requerido",
                icon: "info"
            });     
            return false;         
        }
        if ($('#edit_periodicity_days').val() == '') {
            $('#edit_periodicity_days').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo Periodo es requerido",
                icon: "info"
            });  
            return false;            
        }
        if ($('#edit_num_payments').val() == '') {
            $('#edit_num_payments').focus();
            Swal.fire({
                title: "Campo requerido",
                text: "El campo # Pagos es requerido",
                icon: "info"
            });  
            return false;            
        }

        $.ajax({
            url: "{{ route('types.update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                if(response.status){
                    $('#editType').modal('hide');
                    loadTypes();
                    Swal.fire({icon: "success", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                } else { Swal.fire("Error", response.msg, "error"); }
            },
            error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
        });
    });

    // ===============================
    // Eliminar cliente
    // ===============================
    $(document).on("click", ".type-eliminar", function(e){
        e.preventDefault();
        let id = $(this).data("id");
        let name = $(this).data("name");

        Swal.fire({
            title: "Eliminar tipo",
            text: "¿Estás seguro de eliminar a " + name + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: "{{ route('types.delet') }}",
                    type: "POST",
                    data: {id: id, _token: "{{ csrf_token() }}"},
                    success: function(response){
                        if(response.status){
                            loadTypes();
                            Swal.fire({icon:"success", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                        } else {
                            Swal.fire({icon:"error", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                        }
                    },
                    error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
                });
            }
        });
    });
</script>

@endsection
