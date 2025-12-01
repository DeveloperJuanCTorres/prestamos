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
                    <p><strong>% Interés:</strong> {{ number_format($loan->interest_percent,2) }}%</p>
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
            </div>

            <h5>Cronograma de pagos</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th># Cuota</th>
                            <th>Fecha vencimiento</th>
                            <th>Monto</th>
                            <th>Pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loan->payments as $i => $p)
                            <tr>
                                <td>{{ $p->cuota }}</td>
                                <td>{{ $p->due_date }}</td>
                                <td>S/. {{ number_format($p->amount,2) }}</td>
                                <td>
                                    @if($p->paid)
                                        <!-- <span class="badge bg-success">PAGADO</span> -->

                                        <button class="btn btn-dark btn-sm btn-print-ticket" data-id="{{ $p->id }}">
                                            <i class="fa fa-print"></i> Ticket
                                        </button>

                                    @else
                                        <button class="btn btn-primary btn-sm btn-pay" data-id="{{ $p->id }}">
                                            Pagar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
@endpush
@endsection
