@extends('admin.layouts.admin')

@section('title', trans('shop::admin.gateways.title'))

@push('footer-scripts')
    <script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
    <script>
        const sortable = Sortable.create(document.getElementById('gateways'), {
            animation: 150,
            group: 'gateways',
            handle: '.sortable-btn',
        });

        function serialize(sortable) {
            return [].slice.call(sortable.children).map(function (child) {
                return child.dataset['id'];
            });
        }

        const saveButton = document.getElementById('save');

        saveButton.addEventListener('click', function () {
            saveButton.setAttribute('disabled', '');

            axios.post('{{ route('shop.admin.gateways.positions') }}', {
                'gateways': serialize(sortable.el)
            }).then(function (json) {
                createAlert('success', json.data.message, true);
            }).catch(function (error) {
                createAlert('danger', error.response.data.message ? error.response.data.message : error, true)
            }).finally(function () {
                saveButton.removeAttribute('disabled');
            });
        });
    </script>
@endpush

@section('content')
    @if(! $gateways->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">
                    {{ trans('shop::admin.gateways.current') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row" id="gateways">

                    @foreach($gateways as $gateway)
                        <div class="col-md-3 sortable" data-id="{{ $gateway->id }}">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">{{ $gateway->name }}</div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <img src="{{ $gateway->paymentMethod()->image() }}" style="max-height: 45px" class="img-fluid" alt="{{ $gateway->name }}">
                                    </div>

                                    <a href="{{ route('shop.admin.gateways.edit', $gateway) }}" class="btn btn-primary mt-1">
                                        <i class="bi bi-pencil-square"></i> {{ trans('messages.actions.edit') }}
                                    </a>
                                    <a href="{{ route('shop.admin.gateways.destroy', $gateway) }}" class="btn btn-danger mt-1" data-confirm="delete">
                                        <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                                    </a>

                                    @if($gateways->count() > 1)
                                        <span class="btn btn-secondary mt-1 sortable-btn" title="{{ trans('shop::messages.actions.move') }}">
                                            <i class="bi bi-arrows-move"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                @if($gateways->count() > 1)
                    <button type="button" class="btn btn-success" id="save">
                        <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                        <span class="spinner-border spinner-border-sm btn-spinner" role="status"></span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    @if(! $paymentMethods->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">
                    {{ trans('shop::admin.gateways.add') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="typeSelect">{{ trans('messages.fields.type') }}</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="typeSelect" name="type" required>
                        @foreach($paymentMethods as $paymentMethod)
                            <option value="{{ route('shop.admin.gateways.create', $paymentMethod) }}">{{ $paymentMethod }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="#" onclick="this.href = document.getElementById('typeSelect').value" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                </a>
            </div>
        </div>
    @endif
@endsection
