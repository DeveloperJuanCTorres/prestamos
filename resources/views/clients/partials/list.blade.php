@foreach($clients as $client)
    <tr>
        <td>{{$client->tipo_doc}}</td>
        <td>{{$client->numero_doc}}</td>
        <td>{{$client->name}}</td>
        <td>{{$client->address}}</td>
        <td>{{$client->email}}</td>
        <td>{{$client->phone}}</td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-primary btn-sm" type="button" id="dropdownMenuButton{{ $client->id }}" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-h"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                    <!-- <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-eye me-2"></i> Ver
                        </a>
                    </li> -->
                    <li>
                        <a class="dropdown-item client-edit" href="javascript:void(0);"
                            data-id="{{ $client->id }}">
                            <i class="fa fa-pencil-square me-2"></i> Editar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item client-eliminar" href="javascript:void(0);"
                            data-id="{{ $client->id }}"
                            data-name="{{ $client->name }}">
                            <i class="fa fa-trash me-2"></i> Eliminar
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach