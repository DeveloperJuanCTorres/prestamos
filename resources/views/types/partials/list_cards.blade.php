@foreach($types as $type)
<div class="card mb-2 shadow-sm">
    <div class="card-body p-2">

        <div class="d-flex justify-content-between align-items-center">
            <strong>{{ $type->name }}</strong>
            <span class="badge badge-warning">
                {{ $type->num_payments }} cuotas
            </span>
        </div>

        <div class="mt-2 small">
            <div><strong>Mínimo:</strong> {{ $type->minimo }}%</div>
            <div><strong>Máximo:</strong> {{ $type->maximo }}%</div>
            <div><strong>Periodo:</strong> {{ $type->periodicity_days }} días</div>
        </div>

        <div class="mt-2 d-flex justify-content-end">
            <button class="btn btn-sm btn-info mr-1 type-edit"
                data-id="{{ $type->id }}">
                <i class="fa fa-pencil-square px-2"></i>Editar
            </button>

            <button class="btn btn-sm btn-danger type-eliminar"
                data-id="{{ $type->id }}"
                data-name="{{ $type->name }}">
                <i class="fa fa-trash px-2"></i>Eliminar
            </button>
        </div>

    </div>
</div>
@endforeach