@foreach($clients as $client)
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">{{ $client->name }}</h5>
        <p class="mb-1"><strong>Tipo Doc:</strong> {{ $client->tipo_doc }}</p>
        <p class="mb-1"><strong>Número Doc:</strong> {{ $client->numero_doc }}</p>
        <p class="mb-1"><strong>Dirección:</strong> {{ $client->address }}</p>
        <p class="mb-1"><strong>Email:</strong> {{ $client->email }}</p>
        <p class="mb-1"><strong>Teléfono:</strong> {{ $client->phone }}</p>
        <div class="mt-2">
            <button class="btn btn-sm btn-primary client-edit" data-id="{{ $client->id }}">Editar</button>
            <button class="btn btn-sm btn-danger client-eliminar" data-id="{{ $client->id }}" data-name="{{ $client->name }}">Eliminar</button>
        </div>
    </div>
</div>
@endforeach
