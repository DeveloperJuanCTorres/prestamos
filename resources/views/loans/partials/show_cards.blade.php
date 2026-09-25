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
                    <span>{{ $p->fecha_vencimiento_formatted }}</span>
                </div>

                @if($p->isPaid())
                <div class="d-flex justify-content-between mb-2">
                    <strong>F. Pago:</strong>
                    <span>{{ $p->fecha_pago_formatted }}</span>
                </div>
                @endif

                @if($p->dias_atraso > 0)
                <div class="d-flex justify-content-between mb-2">
                    <strong>Atraso:</strong>
                    <span class="badge badge-danger" style="background-color: #dc3545; color: white;">{{ $p->dias_atraso }} días</span>
                </div>
                @endif

                <div class="d-flex justify-content-between mb-2">
                    <strong>Monto:</strong>
                    <span>S/. {{ number_format($p->amount,2) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <strong>Estado:</strong>

                    @if($p->status === 'paid' || ($p->status !== 'cancelled' && $p->paid == 1))
                        <div class="d-flex gap-2">
                            <button class="btn btn-dark btn-sm btn-print-ticket" data-id="{{ $p->id }}">
                                <i class="fa fa-print"></i> Ticket
                            </button>

                            <!-- <button
                                class="btn btn-success btn-sm btn-whatsapp-ticket"
                                data-id="{{ $p->id }}">
                                <i class="fa fa-whatsapp"></i> WhatsApp
                            </button> -->
                            <button class="btn btn-success btn-sm btn-share-pdf"
                                    data-id="{{ $p->id }}">
                                <i class="fa fa-whatsapp"></i>
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
                    @elseif($p->status === 'cancelled')
                        <span class="badge badge-secondary" style="background-color: #6c757d; color: white; padding: 5px 10px;">ANULADO</span>
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
