@extends('layouts.app')

@section('title', trans('auth.login'))

@section('content')
    <h1>{{ trans('auth.login') }}</h1>

    <div class="row" id="shop">
        <div class="col-lg-3">
            @include('shop::categories._sidebar')
        </div>

        <div class="col-lg-9 text-center">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('shop.login') }}" class="col-md-6 mx-auto">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">{{ trans('auth.name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary mx-auto">
                            <i class="bi bi-box-arrow-in-right"></i> {{ trans('auth.login') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
