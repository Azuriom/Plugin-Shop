@extends('admin.layouts.admin')

@section('title', trans('shop::admin.goals.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <p>{{ trans('shop::admin.goals.info') }}</p>

            <p>{{ trans('shop::admin.goals.total', ['total' => $total]) }}</p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('shop::admin.goals.amount') }}</th>
                        <th scope="col">{{ trans('shop::admin.goals.reached') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($tiers as $tier)
                        <tr>
                            <th scope="row">{{ $tier->id }}</th>
                            <td>
                                @if($tier->icon)
                                    <i class="{{ $tier->icon }}"></i>
                                @endif
                                {{ $tier->name }}
                            </td>
                            <td>{{ $tier->formatAmount() }}</td>
                            <td>
                                <span class="badge bg-{{ $tier->isReached() ? 'success' : 'secondary' }}">
                                    {{ trans_bool($tier->isReached()) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.goals.edit', $tier) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('shop.admin.goals.destroy', $tier) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.goals.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
