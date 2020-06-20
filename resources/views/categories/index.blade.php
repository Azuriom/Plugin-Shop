@extends('layouts.app')

@section('title', trans('shop::messages.title'))

@section('content')
    <div class="container content">
    	@include('shop::navbar.nav')
        <div class="alert alert-warning" role="alert">
            {{ trans('shop::messages.categories.empty') }}
        </div>
    </div>
@endsection