@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>

        <div class="input-group @error('name') has-validation @enderror">
            <span class="input-group-text">{</span>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $variable->name ?? '') }}" required @readonly(isset($variable))>
            <span class="input-group-text">}</span>

            @error('name')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div id="nameLabel" class="form-text">{{ trans('shop::admin.variables.name') }}</div>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="typeSelect">{{ trans('messages.fields.type') }}</label>

        <select class="form-select" id="typeSelect" name="type" required v-model="type">
            @foreach($types as $type)
                <option value="{{ $type }}">
                    {{ trans('shop::admin.variables.'.$type) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="descriptionInput">{{ trans('messages.fields.description') }}</label>
    <input type="text" class="form-control @error('description') is-invalid @enderror" id="descriptionInput" name="description" value="{{ old('description', $variable->description ?? '') }}" required>

    @error('description')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3" v-if="type === 'dropdown'">
    <h3>{{ trans('shop::admin.variables.options') }}</h3>

    <div v-for="(option, i) in options" class="row gx-3">
        <div class="mb-3 col-md-6">
            <label class="form-label" :for="'optionNameInput' + i">{{ trans('messages.fields.name') }}</label>

            <input type="text" class="form-control" :id="'optionNameInput' + i" :name="`options[${i}][name]`" v-model.trim="option.name" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label" :for="'optionValueInput' + i">{{ trans('messages.fields.value') }}</label>

            <div class="input-group">
                <input type="text" class="form-control" :id="'optionValueInput' + i" :name="`options[${i}][value]`" v-model.trim="option.value" required>

                <button type="button" v-if="options.length > 1" class="btn btn-danger" @click="options.splice(i, 1)" title="{{ trans('messages.actions.delete') }}">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <button type="button" @click="options.push({ name: '', value: '' })" class="btn btn-sm btn-success">
        <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
    </button>
</div>

<div v-else-if="type === 'text'">
    <div class="row gx-3">
        <div class="mb-3 col-md-3">
            <label class="form-label" for="minInput">{{ trans('shop::admin.variables.min') }}</label>
            <input type="number" min="0" max="100" class="form-control @error('min') is-invalid @enderror" id="minInput" name="min"
                   placeholder="0" value="{{ old('min', Arr::get($variable->validation ?? [], 'min')) }}">

            @error('min')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="mb-3 col-md-3">
            <label class="form-label" for="maxInput">{{ trans('shop::admin.variables.max') }}</label>
            <input type="number" min="0" max="100" class="form-control @error('max') is-invalid @enderror" id="maxInput" name="max"
                   placeholder="100" value="{{ old('max', Arr::get($variable->validation ?? [], 'max')) }}">

            @error('max')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label" for="filterSelect">{{ trans('shop::admin.variables.filter') }}</label>

            <select class="form-select @error('filter') is-invalid @enderror" id="filterSelect" name="filter" v-model="filter">
                <option value="">{{ trans('messages.none') }}</option>
                @foreach(['alpha', 'alpha_num', 'regex'] as $filter)
                    <option value="{{ $filter }}">
                        {{ trans('shop::admin.variables.'.$filter) }}
                    </option>
                @endforeach
            </select>

            @error('filter')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div v-if="filter === 'regex'" class="mb-3">
        <label class="form-label" for="regexInput">{{ trans('shop::admin.variables.regex') }}</label>
        <input type="text" max="" class="form-control @error('regex') is-invalid @enderror" id="regexInput" name="regex"
               placeholder="/^[A-Za-z\s]+$/" value="{{ old('regex', Arr::get($variable->validation ?? [], 'regex')) }}" required>

        @error('regex')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3" v-if="type === 'server'">
    <label class="form-label" for="serversSelect">{{ trans('shop::admin.variables.options') }}</label>

    <div class="row g-3">
        @foreach($servers as $server)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input @error('options') is-invalid @enderror" id="server{{ $server->id }}"
                           name="options[]" value="{{ $server->id }}" @checked(in_array($server->id, $variable->options ?? []))>
                    <label class="form-check-label" for="server{{ $server->id }}">
                        {{ $server->name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    @error('options')
    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div v-show="type !== 'server'" class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="requiredSwitch" name="is_required" @checked(old('is_required', $variable->is_required ?? false))>
    <label class="form-check-label" for="requiredSwitch">{{ trans('shop::admin.variables.required') }}</label>
</div>

@push('footer-scripts')
    <script>
        const shopVariableOptions = @json(old('options', $variable->options ?? [['name' => '', 'value' => '']]));
        const variableFilter = '{{ old('filter', Arr::get($variable->validation ?? [], 'filter')) }}';
    </script>
@endpush
