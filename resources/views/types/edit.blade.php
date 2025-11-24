<!-- Modal -->
<div class="modal fade" id="editType" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-ml">
        <div class="modal-content bg-theme bg-theme-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Editar Tipo</h5>
                <button type="button" class="btn btn-close" data-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <form id="formEditType" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit_id" name="id"> <!-- id oculto -->
                    <div class="row">                        
                        <div class="col-lg-12 col-md-12 col-12">
                            <div class="form-group">
                                <label for="name">Tipo</label>
                                <input class="form-control" type="text" id="edit_name" name="name" placeholder="Ingrese tipo">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="minimo">% Mínimo</label>
                                <input class="form-control" type="text" id="edit_minimo" name="minimo" placeholder="Ingrese % mínimo" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div>
                        <div class="col-lg-6 col-6 mb-3">
                            <div class="form-group">
                                <label for="maximo" class="form-label">% Máximo</label>
                                <input type="text" class="form-control" id="edit_maximo" name="maximo" placeholder="Ingrese % máximo" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div> 
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="periodo" class="form-label">Periodo</label>
                                <input type="text" class="form-control" id="edit_periodo" name="periodo" placeholder="Ingrese periodo en días" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div>      
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="num_payments" class="form-label"># Pagos</label>
                                <input type="text" class="form-control" id="edit_num_payments" name="num_payments" placeholder="Ingrese # pagos" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div>                    
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEditType" class="btn btn-success">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>







