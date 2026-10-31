@extends('admin.layouts.admin')

@section('title', trans('shop::admin.goals.edit', ['tier' => $tier->name]))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.goals.update', $tier) }}" method="POST">
                @method('PUT')

                @include('shop::admin.goals._form')

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>

                <a href="{{ route('shop.admin.goals.destroy', $tier) }}" class="btn btn-danger" data-confirm="delete">
                    <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                </a>
            </form>
        </div>
    </div>

    @if(! $completions->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">{{ trans('shop::admin.goals.completions') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('shop::admin.goals.reached_at') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.total') }}</th>
                            <th scope="col">{{ trans('shop::admin.goals.participants') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($completions as $completion)
                            <tr>
                                <td>{{ format_date($completion->created_at, true) }}</td>
                                <td>{{ $completion->formatTotal() }}</td>
                                <td>{{ $completion->participants }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
