<!-- Modal -->
<div class="modal fade" id="createType" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-ml">
        <div class="modal-content bg-theme bg-theme-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Nuevo tipo de pago</h5>
                <button type="button" class="btn btn-close" data-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <form id="formAgregar">
                    <div class="row">                        
                        <div class="col-lg-12 col-md-12 col-12">
                            <div class="form-group">
                                <label for="name">Tipo</label>
                                <input class="form-control" type="text" id="name" name="name" placeholder="Ingrese tipo">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="minimo">% Mínimo</label>
                                <input class="form-control" type="text" id="minimo" name="minimo" placeholder="Ingrese % mínimo" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="maximo" class="form-label">% Máximo</label>
                                <input type="text" class="form-control" id="maximo" name="maximo" placeholder="Ingrese % mínimo" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div> 
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="periodo" class="form-label">Periodo</label>
                                <input type="text" class="form-control" id="periodicity_days" name="periodicity_days" placeholder="Ingrese periodo en días" maxlength="2"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            </div>
                        </div>   
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-group">
                                <label for="num_payments" class="form-label"># Pagos</label>
                                <input type="text" class="form-control" id="num_payments" name="num_payments" placeholder="Ingrese # pagos" maxlength="2"
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









