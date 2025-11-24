@extends('layouts.app')

@section('content')

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
                                <h5 class="card-title m-0">Listado de Clientes</h5>
                                <a href="#" class="btn btn-success" id="btnCreate"><i class="fa fa-plus px-2"></i>Nuevo</a>
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

                            <div class="table-responsive" id="clients-container">
                                <table class="table table-striped table-bordered w-100">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%;">Tipo Doc.</th>
                                            <th style="width: 12%;">Número Doc.</th>
                                            <th style="width: 18%;">Nombres</th>
                                            <th style="width: 20%;">Dirección</th>
                                            <th style="width: 15%;">Email</th>
                                            <th style="width: 15%;">Teléfono</th>
                                            <th style="width: 10%;">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @include('clients.partials.list')
                                    </tbody>
                                </table>
                            </div>

                            <!-- Contenedor de paginación -->
                            <div id="pagination-container">
                                {{ $clients->links() }}
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
    @include('clients.create')
    @include('clients.edit')

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // ===============================
    // Función para cargar clientes
    // ===============================
    function loadClients(page = 1) {
        let search = $("#search").val();
        let perPage = $("#perPage").val();

        $.ajax({
            url: "{{ route('clients.list') }}",
            type: "GET",
            data: { search: search, perPage: perPage, page: page },
            dataType: "json",
            success: function(data) {
                // Actualizar tabla y paginación
                $("#clients-container tbody").html(data.table);
                $("#pagination-container").html(data.pagination);
            }
        });
    }

    // ===============================
    // Buscar al escribir
    // ===============================
    $("#search").on("keyup", function() { loadClients(); });

    // ===============================
    // Cambiar cantidad de registros
    // ===============================
    $("#perPage").on("change", function() { loadClients(); });

    // ===============================
    // Paginación
    // ===============================
    $(document).on("click", "#pagination-container a", function(e){
        e.preventDefault();
        let page = $(this).attr("href").split('page=')[1];
        loadClients(page);
    });

    // ===============================
    // Crear cliente
    // ===============================
    $(document).on("click", "#btnCreate", function(){
        $('#createClient').modal('show');
    });

    $(document).on("click", ".registrar", function(e){
        e.preventDefault();
        let formData = new FormData($("#formAgregar")[0]);

        $.ajax({
            url: "{{ route('clients.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(response){
                if(response.status){
                    $("#createClient").modal("hide");
                    $("#formAgregar")[0].reset();
                    loadClients();
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
    $(document).on("click", ".client-edit", function(e){
        e.preventDefault();
        let id = $(this).data("id");

        $.ajax({
            url: "{{ route('clients.edit') }}",
            type: "POST",
            data: {id: id, _token: "{{ csrf_token() }}"},
            success: function(response){
                if(response.status){
                    let c = response.contact;
                    $('#edit_id').val(c.id);
                    $('#edit_tipo_doc').val(c.tipo_doc);
                    $('#edit_numero_doc').val(c.numero_doc);
                    $('#edit_name').val(c.name);
                    $('#edit_address').val(c.address);
                    $('#edit_email').val(c.email);
                    $('#edit_phone').val(c.phone);
                    $('#editClient').modal('show');
                } else {
                    Swal.fire({icon: "error", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                }
            },
            error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
        });
    });

    $(document).on("submit", "#formEditClient", function(e){
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('clients.update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                if(response.status){
                    $('#editClient').modal('hide');
                    loadClients();
                    Swal.fire({icon: "success", title: response.msg, toast:true, position:"top-end", timer:3000, showConfirmButton:false});
                } else { Swal.fire("Error", response.msg, "error"); }
            },
            error: function(){ Swal.fire("Error", "Hubo un problema en el servidor", "error"); }
        });
    });

    // ===============================
    // Eliminar cliente
    // ===============================
    $(document).on("click", ".client-eliminar", function(e){
        e.preventDefault();
        let id = $(this).data("id");
        let name = $(this).data("name");

        Swal.fire({
            title: "Eliminar cliente",
            text: "¿Estás seguro de eliminar a " + name + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: "{{ route('clients.delet') }}",
                    type: "POST",
                    data: {id: id, _token: "{{ csrf_token() }}"},
                    success: function(response){
                        if(response.status){
                            loadClients();
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
