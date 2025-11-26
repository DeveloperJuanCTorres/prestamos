@foreach($types as $type)
    <tr>
        <td>{{$type->id}}</td>
        <td>{{$type->name}}</td>
        <td>{{$type->minimo}}%</td>
        <td>{{$type->maximo}}%</td>
        <td>{{$type->periodicity_days}} días</td>
        <td>{{$type->num_payments}}</td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-primary btn-sm" type="button" id="dropdownMenuButton{{ $type->id }}" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-h"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $type->id }}">
                    <!-- <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-eye me-2"></i> Ver
                        </a>
                    </li> -->
                    <li>
                        <a class="dropdown-item type-edit" href="javascript:void(0);"
                            data-id="{{ $type->id }}">
                            <i class="fa fa-pencil-square me-2"></i> Editar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item type-eliminar" href="javascript:void(0);"
                            data-id="{{ $type->id }}"
                            data-name="{{ $type->name }}">
                            <i class="fa fa-trash me-2"></i> Eliminar
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@endforeach