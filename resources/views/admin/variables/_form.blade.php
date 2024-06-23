@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>

        <div class="input-group @error('name') has-validation @enderror">
            <span class="input-group-text">
                {
            </span>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $variable->name ?? '') }}" required @readonly(isset($variable))>
            <span class="input-group-text">
                }
            </span>

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

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="requiredSwitch" name="is_required" @checked(old('is_required', $variable->is_required ?? false))>
    <label class="form-check-label" for="requiredSwitch">{{ trans('shop::admin.variables.required') }}</label>
</div>

@push('footer-scripts')
    <script>
        const shopVariableOptions = @json(old('options', $variable->options ?? [['name' => '', 'value' => '']]));
    </script>
@endpush
