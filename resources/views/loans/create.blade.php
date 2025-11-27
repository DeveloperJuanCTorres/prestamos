@extends('layouts.app')

@section('content')
<style>
    #select2-custom {
        cursor: pointer;
        /* background: white; */
        min-height: 38px;
    }

    #select2-dropdown .select2-item {
        color: #333;
        padding: 8px 12px;
        cursor: pointer;
        
    }

    #select2-dropdown .select2-item:hover {
        background: linear-gradient(45deg, #29323c, #485563);
        color: white;
    }

    #select2-dropdown .disabled {
        color: black;
        pointer-events: none;
        color: white;
    }
</style>

    <div id="wrapper" style="min-height: 100vh;">

        @include('partials.sidebar')
        @include('partials.topbar')

        <div class="clearfix"></div>

        <div class="content-wrapper">
            <div class="container-fluid">
                <h4>Crear Préstamo</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('loans.store') }}" method="POST" id="loanForm">
                    @csrf

                    <div class="mb-3 position-relative">
                        <label class="form-label">Cliente</label>

                        <div id="select2-custom" class="form-control d-flex align-items-center" tabindex="0">
                            <span id="select2-text">-- Seleccionar cliente --</span>
                            <input type="hidden" name="client_id" id="client_id">
                            <span class="ms-auto">▾</span>
                        </div>

                        <div id="select2-dropdown" class="border rounded shadow bg-white"
                            style="display:none; position:absolute; width:100%; z-index:9999;">

                            <input type="text" id="select2-search" class="form-control border-0 border-bottom" style="background-color: black;"
                                placeholder="Buscar cliente por nombre o DNI..." autocomplete="off">

                            <div id="select2-results" style="max-height:260px; overflow-y:auto;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="amount" class="form-label">Monto (S/.)</label>
                            <input type="text" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required maxlength="4"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="type_id" class="form-label">Tipo de pago</label>
                            <select name="type_id" id="type_id" class="form-control" required>
                                <option value="">-- Seleccionar tipo --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" data-min="{{ $type->minimo }}" data-max="{{ $type->maximo }}" data-days="{{ $type->periodicity_days }}" data-numpay="{{ $type->num_payments }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->num_payments }} cuotas)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="interest_percent" class="form-label">% Interés</label>
                            <input type="text" name="interest_percent" id="interest_percent" class="form-control" value="{{ old('interest_percent') }}" required required maxlength="2"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                            <small id="interestHelp" class="form-text text-white">El rango permitido aparecerá al seleccionar el tipo.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-success" type="submit">Guardar Préstamo</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i></a>

        @include('partials.footer')
        @include('partials.config')

    </div>
    @push('scripts')

    

    <script>
        $(document).ready(function(){
            function updateInterestHelp(min, max, days, numpay) {
                $('#interestHelp').text('Rango permitido: ' + min + '% — ' + max + '%. Periodicidad: ' + days + ' días. Cuotas: ' + numpay + '.');
                $('#interest_percent').attr('min', min);
                $('#interest_percent').attr('max', max);
            }

            // Si hay un tipo seleccionado al cargar
            let selected = $('#type_id').val();
            if (selected) {
                let option = $('#type_id option:selected');
                updateInterestHelp(option.data('min'), option.data('max'), option.data('days'), option.data('numpay'));
            }

            $('#type_id').on('change', function(){
                let id = $(this).val();
                if (!id) {
                    $('#interestHelp').text('El rango permitido aparecerá al seleccionar el tipo.');
                    $('#interest_percent').removeAttr('min max');
                    return;
                }

                // Podemos usar el data- del option o pedir al servidor
                let opt = $('#type_id option:selected');
                let min = opt.data('min');
                let max = opt.data('max');
                let days = opt.data('days');
                let numpay = opt.data('numpay');

                // Si quieres llamar al servidor en lugar de data-: usar fetch a route('types.limits')
                updateInterestHelp(min, max, days, numpay);
            });

            // Validación preventiva antes de enviar
            $('#loanForm').on('submit', function(e){
                let min = parseFloat($('#interest_percent').attr('min'));
                let max = parseFloat($('#interest_percent').attr('max'));
                let val = parseFloat($('#interest_percent').val());

                if (!isNaN(min) && !isNaN(max)) {
                    if (val < min || val > max) {
                        e.preventDefault();
                        alert('El % de interés debe estar entre ' + min + '% y ' + max + '%.');
                        return false;
                    }
                }
            });
        });
    </script>
    @endpush
@endsection


