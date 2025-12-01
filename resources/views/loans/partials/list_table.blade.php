@foreach($loans as $loan)
    <tr>
        <td>{{$loan->id}}</td>
        <td>{{$loan->client?->name ?? 'Cliente eliminado'}}</td>
        <td>{{$loan->client?->phone ?? '---------'}}</td>
        <td>S/. {{$loan->amount}}</td>
        <td>{{$loan->interest_percent}}%</td>
        <td>
            <span class="badge badge-info my-auto">
                {{ $loan->num_payments }} cuotas
            </span>
        </td>
        <td>S/. {{$loan->total_to_pay}}</td>
        <td class="text-center">
            @if ($loan->estado == 'pagado')
                <span class="badge badge-success">PAGADO</span>
            @else
                <span class="badge badge-danger">PENDIENTE</span>
            @endif
        </td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-primary btn-sm" type="button" id="dropdownMenuButton{{ $loan->id }}" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-h"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $loan->id }}">
                    <li>
                        <a class="dropdown-item" href="{{ route('loans.show', $loan->id) }}">
                            <i class="fa fa-eye me-2"></i> Detalle
                        </a>
                    </li>
                    @if (!$loan->hasAnyPaidPayment())
                        <li>
                            <a class="dropdown-item" href="{{ route('loans.edit', $loan->id) }}">
                                <i class="fa fa-pencil me-2"></i> Editar
                            </a>
                        </li>
                    @endif
                    <!-- <li>
                        <a class="dropdown-item loan-edit" href="javascript:void(0);"
                            data-id="{{ $loan->id }}">
                            <i class="fa fa-pencil-square me-2"></i> Editar
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item loan-eliminar" href="javascript:void(0);"
                            data-id="{{ $loan->id }}"
                            data-name="{{ $loan->name }}">
                            <i class="fa fa-trash me-2"></i> Eliminar
                        </a>
                    </li> -->
                </ul>
            </div>
        </td>
    </tr>
@endforeach