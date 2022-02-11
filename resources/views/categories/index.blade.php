@extends('layouts.app')

@section('title', trans('shop::messages.title'))


@section('content')
    <div class="container content">
        <div class="row">
            <div class="col-lg-3">
                @include('shop::categories.sidebar')
            </div>

            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">{{ trans('shop::messages.title') }}</h4>
                        <div class="card-text">
                            {{ $welcome }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
