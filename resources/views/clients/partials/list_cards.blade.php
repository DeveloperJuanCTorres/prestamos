@foreach($clients as $client)
<div class="card mb-3 shadow-sm">
    <div class="card-body p-2">
        <h5 class="card-title">{{ $client->name }}</h5>
        <p class="mb-1"><strong>Tipo Doc:</strong> {{ $client->tipo_doc }}</p>
        <p class="mb-1"><strong>Número Doc:</strong> {{ $client->numero_doc }}</p>
        <p class="mb-1"><strong>Dirección:</strong> {{ $client->address }}</p>
        <p class="mb-1"><strong>Email:</strong> {{ $client->email }}</p>
        <p class="mb-1"><strong>Teléfono:</strong> {{ $client->phone }}</p>

        <div class="mt-2 d-flex justify-content-end">
            <button class="btn btn-sm btn-info mr-1 client-edit" data-id="{{ $client->id }}">
                <i class="fa fa-pencil-square px-2"></i>Editar
            </button>
            <button class="btn btn-sm btn-danger client-eliminar" data-id="{{ $client->id }}" data-name="{{ $client->name }}">
                <i class="fa fa-trash px-2"></i>Eliminar
            </button>
        </div>
    </div>
</div>
@endforeach
