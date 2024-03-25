@extends('admin.layouts.admin')

@section('title', trans('shop::admin.giftcards.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('shop::messages.fields.code') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.balance') }}</th>
                        <th scope="col">{{ trans('shop::admin.giftcards.active') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($giftcards as $giftcard)
                        <tr>
                            <th scope="row">{{ $giftcard->id }}</th>
                            <td>{{ $giftcard->code }}</td>
                            <td>
                                {{ shop_format_amount($giftcard->balance) }}
                                @if($giftcard->isPending())
                                    <i class="bi bi-hourglass text-warning" title="{{ trans('shop::admin.giftcards.pending') }}" data-bs-toggle="tooltip"></i>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $giftcard->isActive() ? 'success' : 'danger' }}">
                                    {{ trans_bool($giftcard->isActive()) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.giftcards.edit', $giftcard) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('shop.admin.giftcards.destroy', $giftcard) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.giftcards.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
