@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.payments.store') }}" method="POST">
                @include('shop::admin.elements.select')

                @csrf

                <div class="row g-3">
                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="userInput">{{ trans('shop::messages.fields.user_id') }}</label>
                        <input type="number" class="form-control @error('user_id') is-invalid @enderror" id="userInput" name="user_id" value="{{ old('user_id') }}" required>

                        @error('user_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="transactionInput">{{ trans('shop::messages.fields.payment_id') }}</label>
                        <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" id="transactionInput" name="transaction_id" value="{{ old('transaction_id') }}" required>

                        @error('transaction_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="packagesSelect">{{ trans('shop::messages.fields.packages') }}</label>

                        <select class="form-select @error('packages') is-invalid @enderror" id="packagesSelect" name="packages[]" multiple>
                            @foreach($categories as $category)
                                <optgroup label="{{ $category->name }}">
                                    @foreach($category->packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        @error('packages')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection
