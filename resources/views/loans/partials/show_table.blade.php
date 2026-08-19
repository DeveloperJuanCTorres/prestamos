<div class="table-responsive d-none d-md-block">
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
                        @if($p->status === 'paid' || ($p->status !== 'cancelled' && $p->paid == 1))
                            <!-- <span class="badge bg-success">PAGADO</span> -->

                            <button class="btn btn-dark btn-sm btn-print-ticket" data-id="{{ $p->id }}">
                                <i class="fa fa-print"></i> Ticket
                            </button>
                            @php
                                $adminEmails = config('app.admin_usernames');
                                $isAdminUser = auth()->check() && in_array(auth()->user()->email, $adminEmails);
                            @endphp
                            @if($isAdminUser)
                                <form action="{{ route('payments.cancelar', $p->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Seguro que deseas CANCELAR este pago?')">
                                        Cancelar Pago
                                    </button>
                                </form>
                            @endif

                        @elseif($p->status === 'cancelled')
                            <span class="badge badge-secondary" style="background-color: #6c757d; color: white; padding: 5px 10px;">ANULADO</span>
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