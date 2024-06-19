@extends('admin.layouts.admin')

@section('title', trans('shop::admin.variables.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('messages.fields.type') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.required') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($variables as $variable)
                        <tr>
                            <th scope="row">{{ $variable->id }}</th>
                            <td>{{ $variable->name }}</td>
                            <td>{{ trans('shop::admin.variables.'.$variable->type) }}</td>
                            <td>
                                {{ trans_bool($variable->is_required) }}
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.variables.edit', $variable) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('shop.admin.variables.destroy', $variable) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.variables.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i> {{ trans('shop::admin.variables.info') }}
    </div>
@endsection
