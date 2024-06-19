@extends('admin.layouts.admin')

@section('title', trans('shop::admin.variables.edit', ['variable' => $variable->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.variables.update', $variable) }}" method="POST" v-scope="{ type: '{{ old('type', $variable->type) }}', options: shopVariableOptions }">
                @method('PUT')

                @include('shop::admin.variables._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>

                <a href="{{ route('shop.admin.variables.destroy', $variable) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>
@endsection
