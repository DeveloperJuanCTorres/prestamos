@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .select2-container--open .select2-dropdown {
        background: linear-gradient(45deg, #29323c, #485563);
    }

    .select2-container .select2-selection--single {
        height: calc(1.5em + .75rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .375rem;
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
        background: linear-gradient(45deg, #29323c, #485563);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: #495057;
        line-height: normal;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 6px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        background: linear-gradient(45deg, #29323c, #485563);
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.25);
    }

    .select2-container {
        width: 100% !important;
    }
</style>

<div id="wrapper" style="min-height: 100vh;">

    @include('partials.sidebar')
    @include('partials.topbar')

    <div class="clearfix"></div>

    <div class="content-wrapper">
        <div class="container-fluid">
            <h4>Editar Préstamo #{{ $loan->id }}</h4>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('loans.update', $loan->id) }}" method="POST" id="loanForm">
                @csrf
                @method('PUT')

                <!-- CLIENTE (NO EDITABLE) -->
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="{{ $loan->client->name }}" disabled>
                </div>

                <div class="row">
                    <!-- MONTO -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Monto (S/.)</label>
                        <input type="text" name="amount" id="amount" class="form-control"
                               value="{{ old('amount', $loan->amount) }}" required maxlength="9"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);">
                    </div>

                    <!-- TIPO -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de pago</label>
                        <select name="type_id" id="type_id" class="form-control" required>
                            <option value="">-- Seleccionar tipo --</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}"
                                    data-min="{{ $type->minimo }}"
                                    data-max="{{ $type->maximo }}"
                                    data-days="{{ $type->periodicity_days }}"
                                    data-numpay="{{ $type->num_payments }}"
                                    {{ $loan->type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->num_payments }} cuotas)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- INTERÉS -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">% Interés</label>
                        <input type="text" name="interest_percent" id="interest_percent" class="form-control"
                               value="{{ old('interest_percent', $loan->interest_percent) }}" required maxlength="2"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);">
                        <small id="interestHelp" class="form-text text-muted">
                            El rango permitido aparecerá al seleccionar el tipo.
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary" type="submit">Actualizar Préstamo</button>
                    <a href="{{ route('loans.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <a href="javaScript:void();" class="back-to-top">
        <i class="fa fa-angle-double-up"></i>
    </a>

    @include('partials.footer')
    @include('partials.config')
</div>

@push('scripts')
<script>
$(document).ready(function(){

    function updateInterestHelp(min, max, days, numpay) {
        $('#interestHelp').text(
            'Rango permitido: ' + min + '% — ' + max + '%. ' +
            'Periodicidad: ' + days + ' días. Cuotas: ' + numpay + '.'
        );
        $('#interest_percent').attr('min', min);
        $('#interest_percent').attr('max', max);
    }

    // Cargar datos al iniciar
    let option = $('#type_id option:selected');
    if (option.val()) {
        updateInterestHelp(
            option.data('min'),
            option.data('max'),
            option.data('days'),
            option.data('numpay')
        );
    }

    $('#type_id').on('change', function(){
        let opt = $('#type_id option:selected');
        if (!opt.val()) {
            $('#interestHelp').text('Seleccione un tipo.');
            return;
        }

        updateInterestHelp(
            opt.data('min'),
            opt.data('max'),
            opt.data('days'),
            opt.data('numpay')
        );
    });

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
