@extends('layouts.app')

@section('content')

<div id="wrapper" style="min-height: 100vh;">

    @include('partials.sidebar')
    @include('partials.topbar')

    <div class="clearfix"></div>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-lg">
                        <div class="card-header bg-danger text-white">
                            <h5 class="m-0"><i class="fa fa-money"></i> Liquidación de Deuda - Préstamo #{{ $loan->id }}</h5>
                        </div>

                        <div class="card-body">
                            <!-- Información del cliente -->
                            <div class="alert alert-info mb-4">
                                <strong>Cliente:</strong> {{ $loan->client->name }}<br>
                                <strong>Monto original:</strong> ${{ number_format($loan->amount, 2) }}<br>
                                <strong>Tipo de préstamo:</strong> {{ $loan->type->name }}<br>
                                <strong>Tasa de interés:</strong> {{ $loan->interest_percent }}% mensual
                            </div>

                            <!-- Resumen de liquidación (cargado por AJAX) -->
                            <div id="liquidation-summary" class="mb-4">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Cargando...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Advertencia -->
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>⚠️ Atención:</strong> Al confirmar esta liquidación:
                                <ul class="mt-2 mb-0">
                                    <li>Se cancelará el total de la deuda inmediatamente</li>
                                    <li>Las cuotas futuras serán anuladas</li>
                                    <li>No se podrá revertir esta operación</li>
                                </ul>
                            </div>

                            <!-- Formulario de liquidación -->
                            <form id="liquidationForm" method="POST" action="{{ route('loans.liquidate', $loan->id) }}">
                                @csrf

                                <!-- Primera confirmación -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm_1" name="confirm_1" required>
                                    <label class="form-check-label" for="confirm_1">
                                        Confirmo que deseo liquidar la totalidad de la deuda de este préstamo
                                    </label>
                                    @error('confirm_1')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Segunda confirmación (aparece después de la primera) -->
                                <div id="confirm-2-section" class="mb-3" style="display: none;">
                                    <div class="alert alert-danger">
                                        <strong>⚠️ Confirma de nuevo:</strong> Esta es una acción irreversible
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirm_2" name="confirm_2" required>
                                        <label class="form-check-label" for="confirm_2">
                                            <strong>CONFIRMO DEFINITIVAMENTE la liquidación de esta deuda</strong>
                                        </label>
                                        @error('confirm_2')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="mt-4">
                                    <button type="button" id="btnCancel" class="btn btn-secondary mr-2">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" id="btnLiquidate" class="btn btn-danger" disabled>
                                        <i class="fa fa-check"></i> Liquidar Deuda
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i></a>

    @include('partials.footer')

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    $(document).ready(function() {
        // ===============================
        // Cargar resumen de liquidación
        // ===============================
        function loadLiquidationSummary() {
            $.ajax({
                url: "{{ route('loans.liquidation.summary', $loan->id) }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.success) {
                        let html = `
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Resumen de Liquidación</h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Capital pagado:</strong> <span class="text-success">$${parseFloat(data.capital_paid).toFixed(2)}</span></p>
                                            <p><strong>Capital restante:</strong> <span class="text-danger">$${parseFloat(data.capital_remaining).toFixed(2)}</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Cuotas pagadas:</strong> ${data.paid_quotes} / ${data.paid_quotes + data.pending_quotes}</p>
                                            <p><strong>Cuotas a cancelar:</strong> ${data.quotes_to_cancel}</p>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row bg-white p-3 rounded">
                                        <div class="col-md-6">
                                            <p class="text-muted mb-1">Interés a cancelar:</p>
                                            <p class="h5 text-warning">$${parseFloat(data.current_month_interest).toFixed(2)}</p>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <p class="text-muted mb-1"><strong>Total a pagar:</strong></p>
                                            <p class="h4 text-danger"><strong>$${parseFloat(data.total_to_pay).toFixed(2)}</strong></p>
                                        </div>
                                    </div>

                                    <p class="text-muted small mt-2 mb-0">${data.interest_note}</p>
                                </div>
                            </div>
                        `;
                        $("#liquidation-summary").html(html);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        window.location.href = "{{ route('loans.show', $loan->id) }}";
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cargar el resumen de liquidación', 'error');
                }
            });
        }

        // Cargar al abrir
        loadLiquidationSummary();

        // ===============================
        // Primera confirmación
        // ===============================
        $("#confirm_1").on("change", function() {
            if (this.checked) {
                $("#confirm-2-section").slideDown(300);
            } else {
                $("#confirm-2-section").slideUp(300);
                $("#confirm_2").prop("checked", false);
                $("#btnLiquidate").prop("disabled", true);
            }
        });

        // ===============================
        // Segunda confirmación
        // ===============================
        $("#confirm_2").on("change", function() {
            if ($("#confirm_1").is(":checked") && this.checked) {
                $("#btnLiquidate").prop("disabled", false);
            } else {
                $("#btnLiquidate").prop("disabled", true);
            }
        });

        // ===============================
        // Cancelar
        // ===============================
        $("#btnCancel").on("click", function() {
            Swal.fire({
                title: "¿Cancelar operación?",
                text: "Se descartarán todos los cambios",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, cancelar",
                cancelButtonText: "No, continuar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('loans.show', $loan->id) }}";
                }
            });
        });

        // ===============================
        // Enviar formulario
        // ===============================
        $("#liquidationForm").on("submit", function(e) {
            e.preventDefault();

            if (!$("#confirm_1").is(":checked") || !$("#confirm_2").is(":checked")) {
                Swal.fire('Error', 'Se requiere doble confirmación', 'error');
                return false;
            }

            Swal.fire({
                title: "⚠️ Última confirmación",
                html: "<strong>¿CONFIRMAS definitivamente la liquidación de esta deuda?</strong><br><small>Esta acción NO podrá ser revertida</small>",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "SÍ, LIQUIDAR",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Deshabilitar botón
                    $("#btnLiquidate").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

                    // Enviar AJAX
                    $.ajax({
                        url: "{{ route('loans.liquidate', $loan->id) }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            confirm_1: true,
                            confirm_2: true
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data.success) {
                                Swal.fire({
                                    title: "✅ Éxito",
                                    html: `<strong>Deuda liquidada correctamente</strong><br>
                                            <small>Total pagado: $${parseFloat(data.liquidation.total_paid).toFixed(2)}</small><br>
                                            <small>Cuotas canceladas: ${data.liquidation.quotes_cancelled}</small>`,
                                    icon: "success",
                                    confirmButtonText: "Ir al préstamo"
                                }).then(() => {
                                    window.location.href = "{{ route('loans.show', $loan->id) }}";
                                });
                            } else {
                                Swal.fire('Error', data.error || 'Error al liquidar', 'error');
                                $("#btnLiquidate").prop("disabled", false).html('<i class="fa fa-check"></i> Liquidar Deuda');
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al procesar la liquidación';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                            $("#btnLiquidate").prop("disabled", false).html('<i class="fa fa-check"></i> Liquidar Deuda');
                        }
                    });
                }
            });
        });
    });
</script>

@endsection
