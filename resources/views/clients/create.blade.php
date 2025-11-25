<!-- Modal -->
<div class="modal fade" id="createClient" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-ml">
        <div class="modal-content bg-theme bg-theme-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Nuevo Cliente</h5>
                <button type="button" class="btn btn-danger btn-close" data-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <form id="formAgregar">
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-12">
                            <div class="form-group">
                                <label for="tipo_doc">Tipo Documento</label>
                                <select class="form-select form-control" name="tipo_doc" id="tipo_doc">
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="PASAPORTE">PASAPORTE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12">                    
                            <div class="form-group">
                                <label for="tipo_doc">Número Documento</label>  
                                <div class="input-group">                                              
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="numero_doc"
                                        name="numero_doc"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-12">
                            <div class="form-group">
                                <label for="tipo_doc">Nombre</label>
                                <input class="form-control" type="text" id="name" name="name" placeholder="Ingrese cliente">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-12">
                            <div class="form-group">
                                <label for="tipo_doc">Dirección</label>
                                <input class="form-control" type="text" id="address" name="address" placeholder="Ingrese dirección">
                            </div>
                        </div>
                        <div class="col-lg-12 col-12">
                            <div class="form-group">
                                <label for="nombre" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <div class="col-lg-12 col-12">
                            <div class="form-group">
                                <label for="categoria_id" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="phone" name="phone" maxlength="9"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div> 
                        </div>                       
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="formAgregar" class="btn btn-success registrar">Registrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Validar mientras escribe
        $('#numero_doc').on('input', function () {
            let tipo = $('#tipo_doc').val();

            if (tipo === 'DNI' || tipo === 'RUC') {
                // Solo números
                this.value = this.value.replace(/[^0-9]/g, '');
            } else if (tipo === 'PASAPORTE') {
                // Solo alfanumérico (letras y números)
                this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
            }
        });
        
        // Cambiar longitud máxima según tipo_doc
        $('#tipo_doc').on('change', function () {
            let tipo = $(this).val();

            if (tipo === 'DNI') {               
                $('#numero_doc').attr('maxlength', 8);
                $('#numero_doc').val($('#numero_doc').val().slice(0, 8)); // recortar si excede
                $('#numero_doc').value.replace(/[^0-9]/g, ''); // elimina todo lo que no sea número
            } else if (tipo === 'RUC') {                              
                $('#numero_doc').attr('maxlength', 11);
                $('#numero_doc').val($('#numero_doc').val().slice(0, 11)); // recortar si excede
                $('#numero_doc').value.replace(/[^0-9]/g, ''); // elimina todo lo que no sea número
            }
            if (tipo === 'PASAPORTE') {
                $('#numero_doc').attr('maxlength', 20);
                $('#numero_doc').val($('#numero_doc').val().slice(0, 20)); // recortar si excede
            }
        });

        
            $('#phone').attr('maxlength', 9);
        

        // Forzar la longitud inicial al abrir modal (por si cambia)
        $('#tipo_doc').trigger('change');

        // Forzar reglas al cargar
        $('#tipo_doc').trigger('change');
    });
</script>








