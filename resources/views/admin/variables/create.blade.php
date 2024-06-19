@extends('admin.layouts.admin')

@section('title', trans('shop::admin.variables.create'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.variables.store') }}" method="POST" v-scope="{ type: '{{ old('type') }}', options: shopVariableOptions }">
                @include('shop::admin.variables._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
