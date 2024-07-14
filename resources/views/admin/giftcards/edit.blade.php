@extends('admin.layouts.admin')

@section('title', trans('shop::admin.giftcards.edit', ['giftcard' => $giftcard->code]))

@section('content')
    @if($giftcard->isPending())
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-hourglass"></i> {{ trans('shop::admin.giftcards.pending') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="{{ route('shop.admin.giftcards.update', $giftcard) }}" method="POST">
                        @method('PUT')

                        @include('shop::admin.giftcards._form', ['row' => false])

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                        </button>

                        <a href="{{ route('shop.admin.giftcards.destroy', $giftcard) }}" class="btn btn-danger" data-confirm="delete">
                            <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="codeInput">{{ trans('shop::messages.fields.code') }}</label>
                        <input type="text" class="form-control" id="codeInput" disabled value="{{ $giftcard->code }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="balanceInput">{{ trans('shop::messages.fields.balance') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="balanceInput" disabled value="{{ $giftcard->balance }}">
                            <span class="input-group-text">{{ shop_active_currency() }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="originalBalanceInput">{{ trans('shop::messages.fields.original_balance') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="originalBalanceInput" disabled value="{{ $giftcard->original_balance }}">
                            <span class="input-group-text">{{ shop_active_currency() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                           {{ trans('shop::messages.fields.payments') }}
                        </h5>
                    </div>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{ trans('messages.fields.user') }}</th>
                            <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                            <th scope="col">{{ trans('messages.fields.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($giftcard->payments as $payment)
                            <tr>
                                <th scope="row">
                                    <a href="{{ route('shop.admin.payments.show', $payment) }}">
                                        {{ $payment->id }}
                                    </a>
                                </th>
                                <td>
                                    <a href="{{ route('admin.users.edit', $payment->user) }}">{{ $payment->user->name }}</a>
                                </td>
                                <td>{{ shop_format_amount($payment->pivot->amount) }}</td>
                                <td>
                                    <a href="{{ route('shop.admin.payments.show', $payment) }}" class="mx-1" title="{{ trans('messages.actions.show') }}" data-bs-toggle="tooltip"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(use_site_money())
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="shareableLink">{{ trans('shop::admin.giftcards.link') }}</label>
                            <input type="text" class="form-control" id="shareableLink" value="{{ $giftcard->shareableLink() }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
