@extends('admin.layouts.admin')

@section('title', trans('shop::admin.giftcards.create'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.giftcards.store') }}" method="POST">
                <div class="row gx-3">
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="codeInput">{{ trans('shop::messages.fields.code') }}</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="codeInput" name="code" value="{{ old('code') }}" required>

                        @error('code')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="balanceInput">{{ trans('shop::messages.fields.balance') }}</label>
                        <div class="input-group @error('balance') has-validation @enderror">
                            <input type="number" min="0" class="form-control @error('balance') is-invalid @enderror" id="balanceInput" name="balance" value="{{ old('balance') }}" required>
                            <span class="input-group-text">{{ shop_active_currency() }}</span>

                            @error('balance')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                @include('shop::admin.giftcards._form', ['row' => true])

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
