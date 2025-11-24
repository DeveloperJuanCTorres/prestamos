@extends('layouts.app')

@section('content')
<div id="wrapper">

    @include('partials.sidebar')
    @include('partials.topbar')

    <div class="clearfix"></div>

    <div class="content-wrapper">
        <div class="container-fluid">
            <h4>Préstamo #{{ $loan->id }}</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>Cliente:</strong> {{ $loan->client->name }} ({{ $loan->client->numero_doc }})</p>
                    <p><strong>Tipo:</strong> {{ $loan->type->name }}</p>
                    <p><strong>Monto:</strong> S/. {{ number_format($loan->amount,2) }}</p>
                    <p><strong>% Interés:</strong> {{ number_format($loan->interest_percent,2) }}%</p>
                    <p><strong>Interés total:</strong> S/. {{ number_format($loan->total_to_pay - $loan->amount,2) }}</p>
                    <p><strong>Total a pagar:</strong> S/. {{ number_format($loan->total_to_pay,2) }}</p>
                    <p><strong>Cuotas:</strong> {{ $loan->num_payments }}</p>
                </div>
            </div>

            <!-- Botón imprimir cronograma -->
            <div class="mb-3">
                <a href="{{ route('loans.printSchedule', $loan->id) }}" 
                   class="btn btn-danger" target="_blank">
                    <i class="fa fa-print"></i> Imprimir cronograma
                </a>
            </div>

            <h5>Cronograma de pagos</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha vencimiento</th>
                            <th>Monto</th>
                            <th>Pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loan->payments as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->due_date }}</td>
                                <td>S/. {{ number_format($p->amount,2) }}</td>
                                <td>
                                    @if($p->paid)
                                        <span class="badge bg-success">PAGADO</span>

                                        <a href="{{ route('payments.ticket', $p->id) }}" 
                                        class="btn btn-dark btn-sm" 
                                        target="_blank">
                                            <i class="fa fa-print"></i> Ticket
                                        </a>

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

@section('script')

<script>
document.addEventListener("DOMContentLoaded", function() {
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

                    // Procesar pago
                    window.location.href = "/payments/" + paymentId + "/pay?print=1";

                }
            });
        });
    });
});
</script>
@endsection
@endsection
