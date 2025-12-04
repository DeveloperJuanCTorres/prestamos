<div class="d-block d-md-none">
    @foreach($loan->payments as $p)
        <div class="card mb-3 shadow-sm">
            <div class="card-body p-3">

                <div class="d-flex justify-content-between mb-2">
                    <strong>Cuota:</strong>
                    <span>#{{ $p->cuota }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <strong>Vencimiento:</strong>
                    <span>{{ $p->due_date }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <strong>Monto:</strong>
                    <span>S/. {{ number_format($p->amount,2) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <strong>Estado:</strong>

                    @if($p->paid)
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark btn-sm btn-print-ticket" data-id="{{ $p->id }}">
                                <i class="fa fa-print"></i> Ticket
                            </button>

                            @php
                                $adminEmails = config('app.admin_usernames');
                                $isAdminUser = auth()->check() && in_array(auth()->user()->email, $adminEmails);
                            @endphp

                            @if($isAdminUser)
                                <form action="{{ route('payments.cancelar', $p->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Seguro que deseas CANCELAR este pago?')">
                                        Cancelar
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <button class="btn btn-primary btn-sm btn-pay" data-id="{{ $p->id }}">
                            Pagar
                        </button>
                    @endif
                </div>

            </div>
        </div>
    @endforeach
</div>
