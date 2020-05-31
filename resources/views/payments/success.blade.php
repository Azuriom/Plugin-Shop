@extends('layouts.app')

@section('title', 'Payment')

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.payments.success') }}</h1>

        <p>{{ trans('shop::messages.payments.success-info') }}</p>
    </div>
@endsection
