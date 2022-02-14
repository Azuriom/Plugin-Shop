@extends('admin.layouts.admin')

@section('title', trans('shop::admin.giftcards.title'))

@section('content')
    @if (use_site_money())
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('shop::messages.fields.code') }}</th>
                        <th scope="col">{{ trans('messages.fields.money') }}</th>
                        <th scope="col">{{ trans('messages.fields.enabled') }}</th>
                        <th scope="col">{{ trans('shop::admin.giftcards.active') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($giftcards as $giftcard)
                        <tr>
                            <th scope="row">{{ $giftcard->id }}</th>
                            <td>{{ $giftcard->code }}</td>
                            <td>{{ shop_format_amount($giftcard->amount) }}</td>
                            <td>
                                <span class="badge bg-{{ $giftcard->is_enabled ? 'success' : 'danger' }}">
                                    {{ trans_bool($giftcard->is_enabled) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $giftcard->isActive() ? 'success' : 'danger' }}">
                                    {{ trans_bool($giftcard->isActive()) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.giftcards.edit', $giftcard) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('shop.admin.giftcards.destroy', $giftcard) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.giftcards.create') }}">
                <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
    @else
        <div class="alert alert-warning" role="alert">
            <a href="{{route('shop.admin.settings')}}">{{ trans('shop::admin.settings.use-site-money')}}</a>
        </div>
    @endif

@endsection
