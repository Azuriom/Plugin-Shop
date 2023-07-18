@extends('layouts.app')

@section('title', trans('shop::messages.title'))

@section('content')
    <h1>{{ trans('shop::messages.title') }}</h1>

    <div class="row">
        <div class="col-lg-3">
            @include('shop::categories._sidebar')
        </div>

        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    {{ $welcome }}
                </div>
            </div>
        </div>
    </div>
@endsection
