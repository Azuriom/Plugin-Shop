@extends('admin.layouts.admin')

@section('title', trans('shop::admin.subscriptions.title'))

@section('content')
    <form class="row row-cols-lg-auto g-3 align-items-center" action="{{ route('shop.admin.subscriptions.index') }}" method="GET">
        <div class="mb-3">
            <label for="searchInput" class="visually-hidden">
                {{ trans('messages.actions.search') }}
            </label>

            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="{{ trans('messages.actions.search') }}">

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="card shadow mb-4">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.user') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                        <th scope="col">{{ trans('messages.fields.type') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.package') }}</th>
                        <th scope="col">{{ trans('messages.fields.status') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.subscription_id') }}</th>
                        <th scope="col">{{ trans('messages.fields.date') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.renewal_date') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($subscriptions as $subscription)
                        <tr>
                            <th scope="row">{{ $subscription->id }}</th>
                            <td>
                                <a href="{{ route('admin.users.edit', $subscription->user) }}">
                                    {{ $subscription->user->name }}
                                </a>
                            </td>
                            <td>{{ $subscription->formatPrice() }}</td>
                            <td>{{ $subscription->getTypeName() }}</td>
                            <td>
                                @if($subscription->package !== null)
                                    <a href="{{ route('shop.admin.packages.edit', $subscription->package) }}">
                                        {{ $subscription->package->name }}
                                    </a>
                                @else
                                    {{ trans('messages.unknown') }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $subscription->statusColor() }}">
                                    {{ trans('shop::admin.subscriptions.status.'.$subscription->status) }}
                                </span>
                            </td>
                            <td>{{ $subscription->subscription_id }}</td>
                            <td>{{ format_date_compact($subscription->created_at) }}</td>
                            <td>
                                @if($subscription->ends_at !== null)
                                    {{ format_date_compact($subscription->ends_at) }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.subscriptions.show', $subscription) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>

                                @if($subscription->isActive() && ! $subscription->isCanceled())
                                    <a href="{{ route('shop.admin.subscriptions.destroy', $subscription) }}" class="mx-1" title="{{ trans('shop::messages.actions.cancel') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-x-circle"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            {{ $subscriptions->withQueryString()->links() }}
        </div>
    </div>

    @if(scheduler_running())
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i> @lang('shop::admin.subscriptions.setup')
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i> @lang('shop::admin.packages.scheduler')
        </div>
    @endif
@endsection
