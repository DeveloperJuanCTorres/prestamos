@forelse($loans as $loan)
<div class="card mb-2 shadow-sm">
    <div class="card-body p-2">

        <div class="d-flex justify-content-between">
            <strong>Préstamo #{{ $loan->id }}</strong>
            <span class="badge badge-info my-auto">
                {{ $loan->num_payments }} cuotas
            </span>
        </div>

        <div class="mt-2">
            <div><strong>Cliente:</strong> {{ $loan->client->name ?? '—' }}</div>
            <div><strong>Teléfono:</strong> {{ $loan->client->phone ?? '—' }}</div>
            <div><strong>Monto:</strong> S/. {{ number_format($loan->amount, 2) }}</div>
            <div><strong>Interés:</strong> {{ $loan->interest_percent }} %</div>
            <div><strong>Total:</strong> S/. {{ number_format($loan->total_to_pay, 2) }}</div>
            <div><strong>Estado:</strong> 
                @if ($loan->estado == 'pagado')
                    <span class="badge badge-success">PAGADO</span>
                @else
                    <span class="badge badge-danger">PENDIENTE</span>
                @endif
            </div>
        </div>

        <div class="mt-2 d-flex justify-content-end">
            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-primary mr-1">
                <i class="fa fa-eye px-2"></i>Detalle
            </a>
            @if (!$loan->hasAnyPaidPayment())
            <a class="btn btn-sm btn-warning mr-1" href="{{ route('loans.edit', $loan->id) }}">
                <i class="fa fa-pencil me-2"></i> Editar
            </a>
            @endif
        </div>

    </div>
</div>
@empty
<div class="text-center text-muted">No se encontraron préstamos</div>
@endforelse