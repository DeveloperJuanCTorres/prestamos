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
                        @if($p->paid)
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