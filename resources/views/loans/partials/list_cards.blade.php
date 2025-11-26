@forelse($loans as $loan)
<div class="card mb-2 shadow-sm">
    <div class="card-body p-2">

        <div class="d-flex justify-content-between">
            <strong>Préstamo #{{ $loan->id }}</strong>
            <span class="badge badge-warning my-auto">
                {{ $loan->num_payments }} cuotas
            </span>
        </div>

        <div class="mt-2">
            <div><strong>Cliente:</strong> {{ $loan->client->name ?? '—' }}</div>
            <div><strong>Teléfono:</strong> {{ $loan->client->phone ?? '—' }}</div>
            <div><strong>Monto:</strong> S/. {{ number_format($loan->amount, 2) }}</div>
            <div><strong>Interés:</strong> {{ $loan->interest_percent }} %</div>
            <div><strong>Total:</strong> S/. {{ number_format($loan->total_to_pay, 2) }}</div>
        </div>

        <div class="mt-2 d-flex justify-content-end">
            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-primary mr-1">
                <i class="fa fa-eye px-2"></i>Ver detalle
            </a>
        </div>

    </div>
</div>
@empty
<div class="text-center text-muted">No se encontraron préstamos</div>
@endforelse