@extends('layouts.app')

@section('content')
<div id="wrapper" style="min-height: 100vh;">

    @include('partials.sidebar')
    @include('partials.topbar')

    <div class="clearfix"></div>

    <div class="content-wrapper">
        <div class="container-fluid">
            <h4>Préstamo #{{ $loan->id }}</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>Cliente:</strong> {{ $loan->client?->name ?? 'Cliente eliminado'}} ({{ $loan->client?->numero_doc ?? '---------' }})</p>
                    <p><strong>Fecha:</strong> {{$loan->created_at->format('d/m/Y') }}</p>
                    <p><strong>Tipo:</strong> {{ $loan->type?->name ?? 'Tipo eliminado' }}</p>
                    <p><strong>Monto:</strong> S/. {{ number_format($loan->amount,2) }}</p>
                    <p><strong>Interés:</strong> {{ number_format($loan->interest_percent,2) }}%</p>
                    <p><strong>Interés total:</strong> S/. {{ number_format($loan->total_to_pay - $loan->amount,2) }}</p>
                    <p><strong>Total a pagar:</strong> S/. {{ number_format($loan->total_to_pay,2) }}</p>
                    <p><strong>Cuotas:</strong> {{ $loan->num_payments }}</p>
                </div>
            </div>

            <!-- Botón imprimir cronograma -->
            <div class="mb-3">
                <a href="{{ route('loans.printSchedule', $loan->id) }}?v={{ time() }}"
                class="btn btn-danger"
                target="_blank">
                    <i class="fa fa-print"></i> Imprimir cronograma
                </a>

                @if ($loan->isLiquidated())
                    <!-- Estado: Liquidado -->
                    <span class="badge badge-success ml-2">
                        <i class="fa fa-check-circle"></i> LIQUIDADO
                    </span>
                @elseif ($loan->canBeLiquidated())
                    <!-- Botón: Liquidar Deuda -->
                    <a href="{{ route('loans.liquidation.form', $loan->id) }}" class="btn btn-warning ml-2">
                        <i class="fa fa-money"></i> Liquidar Deuda
                    </a>
                @endif
            </div>

            @if ($loan->isLiquidated() && $loan->liquidation)
                <!-- Sección: Información de Liquidación -->
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                        <h5 class="m-0"><i class="fa fa-check-circle"></i> Información de Liquidación</h5>
                        <div class="mt-2 mt-md-0">
                            <!-- Imprimir Constancia A4 -->
                            <a href="{{ route('loans.liquidation.receipt', $loan->id) }}" target="_blank" class="btn btn-dark btn-sm mr-1 mb-1">
                                <i class="fa fa-file-pdf-o mr-1"></i> Imprimir Constancia A4
                            </a>

                            <!-- Compartir en Celular (WhatsApp / Web Share API) -->
                            <button type="button" class="btn btn-success btn-sm btn-share-liquidation mb-1 d-inline-block d-md-none" data-id="{{ $loan->id }}">
                                <i class="fa fa-whatsapp mr-1"></i> Compartir
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Fecha de liquidación:</strong> {{ $loan->liquidation->liquidation_date->format('d/m/Y H:i') }}</p>
                            <p><strong>Capital liquidado:</strong> S/. {{ number_format($loan->liquidation->principal_paid, 2) }}</p>
                            <p><strong>Interés pagado:</strong> S/. {{ number_format($loan->liquidation->interest_paid, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total pagado:</strong> S/. {{ number_format($loan->liquidation->total_paid, 2) }}</p>
                            <p><strong>Cuotas canceladas:</strong> {{ $loan->liquidation->cuota_vigente - 1 }} restantes</p>
                            <p><strong>Realizado por:</strong> {{ $loan->liquidation->user->name ?? 'Sistema' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <h5>Cronograma de pagos</h5>
            @include('loans.partials.show_table')
            @include('loans.partials.show_cards')

            <a href="{{ route('loans.create') }}" class="btn btn-info">Crear otro préstamo</a>
        </div>
    </div>

    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i></a>

    @include('partials.footer')
    @include('partials.config')

</div>

@push('script')
<!-- Incluir módulo Bluetooth -->
<script src="{{ asset('js/bluetooth-printer.js') }}"></script>
<!-- <script src="{{ asset('bluetooth-printer-adv7010.js') }}"></script> -->

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Manejar clics en botón "Pagar"
        document.querySelectorAll('.btn-pay').forEach(btn => {
            btn.addEventListener('click', function() {
                let paymentId = this.getAttribute('data-id');

                Swal.fire({
                    title: '¿Confirmar pago?',
                    text: "Esta acción marcará la cuota como pagada.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, pagar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {

                        // Enviar pago al backend vía AJAX
                        fetch(`/payments/${paymentId}/pay`, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                "Accept": "application/json"
                            }
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.success) {
                                Swal.fire(
                                    'Pagado',
                                    'La cuota ha sido marcada como pagada.',
                                    'success'
                                ).then(() => {
                                    location.reload(); // Recargar listado
                                });
                            }

                        });
                    }
                });
            });
        });

        // Nueva funcionalidad: Manejar impresión de tickets inteligente
        document.querySelectorAll('.btn-print-ticket').forEach(btn => {
            btn.addEventListener('click', async function() {
                const paymentId = this.getAttribute('data-id');

                try {
                    // Obtener datos del ticket y información del dispositivo
                    const response = await fetch(`/payments/${paymentId}/ticket-data`);
                    const data = await response.json();

                    const { ticket_data, device_info, urls } = data;

                    // Mostrar opciones según capacidades del dispositivo
                    if (device_info.supports_bluetooth) {
                        // Dispositivo compatible con Web Bluetooth
                        showBluetoothOptions(ticket_data, urls);
                    } else if (device_info.is_ios) {
                        // iOS - ofrecer compartir PDF
                        showIOSOptions(urls);
                    } else {
                        // Fallback - abrir PDF
                        window.open(urls.pdf_url, '_blank');
                    }

                } catch (error) {
                    console.error('Error obteniendo datos del ticket:', error);
                    // Fallback en caso de error
                    window.open(`/payments/${paymentId}/ticket`, '_blank');
                }
            });
        });

        /**
         * Mostrar opciones para dispositivos con Bluetooth
         */
        function showBluetoothOptions(ticketData, urls) {
            Swal.fire({
                title: 'Opciones de Impresión',
                text: 'Selecciona cómo quieres imprimir el ticket:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="fa fa-bluetooth"></i> Impresora Bluetooth',
                denyButtonText: '<i class="fa fa-file-pdf"></i> Abrir PDF',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                denyButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    printViaBluetooth(ticketData);
                } else if (result.isDenied) {
                    window.open(urls.pdf_url, '_blank');
                }
            });
        }

        /**
         * Mostrar opciones para iOS
         */
        function showIOSOptions(urls) {
            Swal.fire({
                title: 'Imprimir Ticket',
                text: 'En iOS, abre el PDF para compartir o imprimir via AirPrint',
                icon: 'info',
                confirmButtonText: '<i class="fa fa-share"></i> Abrir PDF',
                confirmButtonColor: '#007bff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(urls.pdf_url, '_blank');
                }
            });
        }

        /**
         * Imprimir via Bluetooth
         */
        async function printViaBluetooth(ticketData) {
            try {
                const printer = window.bluetoothPrinter;

                if (!printer.isSupported) {
                    throw new Error('Tu dispositivo no soporta impresión Bluetooth');
                }

                // Mostrar loading
                Swal.fire({
                    title: 'Conectando...',
                    text: 'Buscando impresora Bluetooth',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Conectar a impresora
                await printer.connect();

                // Imprimir
                await printer.print(ticketData);

                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Ticket enviado a la impresora',
                    icon: 'success',
                    timer: 2000
                });

            } catch (error) {
                console.error('Error imprimiendo:', error);

                Swal.fire({
                    title: 'Error de Impresión',
                    text: error.message || 'No se pudo conectar a la impresora Bluetooth',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Abrir PDF como alternativa',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`/payments/${ticketData.payment_id}/ticket`, '_blank');
                    }
                });
            }
        }
    });
</script>


<!-- <script>
    document.addEventListener('click', function (e) {

        const btn = e.target.closest('.btn-whatsapp-ticket');
        if (!btn) return;

        let id = btn.dataset.id;

        fetch(`/payments/${id}/ticket-whatsapp`)
            .then(res => res.json())
            .then(data => {

                let texto = encodeURIComponent(data.message);
                window.open(`https://wa.me/?text=${texto}`, '_blank');

            });
    });
</script> -->

<script>
document.addEventListener('click', function(e){

    const btn = e.target.closest('.btn-share-pdf');
    if(!btn) return;

    compartirPDF(btn.dataset.id);

});

async function compartirPDF(id) {
    try {
        const response = await fetch(`/payments/${id}/ticket`);
        const blob = await response.blob();

        const file = new File([blob], `ticket_${id}.pdf`, {
            type: 'application/pdf'
        });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: `Ticket ${id}`,
                text: 'Te comparto tu comprobante de pago'
            });
        } else {
            alert('Tu navegador no soporta compartir archivos.');
        }

    } catch (error) {
        console.error(error);
        alert('Error al compartir');
    }
}
</script>

<script>
document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-share-liquidation');
    if(!btn) return;

    let loanId = btn.dataset.id;
    compartirLiquidacion(loanId);
});

async function compartirLiquidacion(loanId) {
    try {
        const receiptUrl = `/loans/${loanId}/liquidation-receipt`;
        const response = await fetch(receiptUrl);
        const blob = await response.blob();

        const file = new File([blob], `constancia_liquidacion_prestamo_${loanId}.pdf`, {
            type: 'application/pdf'
        });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: `Constancia de Liquidación #${loanId}`,
                text: `Constancia de cancelación total de deuda del préstamo #${loanId}.`
            });
        } else {
            const shareText = encodeURIComponent(`*CONSTANCIA DE LIQUIDACIÓN DE DEUDA*\nPréstamo #${loanId}\nSu deuda ha sido 100% liquidada.\nPuede ver su constancia A4 aquí: ${window.location.origin}${receiptUrl}`);
            window.open(`https://api.whatsapp.com/send?text=${shareText}`, '_blank');
        }
    } catch (error) {
        console.error(error);
        alert('Error al compartir la constancia.');
    }
}
</script>


@endpush
@endsection
