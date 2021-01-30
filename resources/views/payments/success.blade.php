@extends('layouts.app')

@section('title', trans('shop::messages.payment.title'))

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.payment.success') }}</h1>

        <p>{{ trans('shop::messages.payment.success-info') }}</p>
    </div>
@endsection
