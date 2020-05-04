@extends('layouts.app')

@section('title', trans('shop::messages.offers.title-select'))

@push('footer-scripts')
    <script>
        document.querySelectorAll('.payment-method').forEach(function (el) {
            el.addEventListener('click', function (ev) {
                ev.preventDefault();

                const form = document.getElementById('submitForm');
                form.action = el.href;
                form.submit();
            });
        });
    </script>
@endpush

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.offers.title-select') }}</h1>

        <div class="row">
            @foreach($offers as $offer)
                <div class="col-md-3">
                    <div class="card shadow-sm mb-3">
                        <a href="{{ route('shop.offers.pay', [$offer, $gateway]) }}" class="payment-method">
                            <div class="card-body text-center">
                                <h3>{{ $offer->name }}</h3>
                                <h4>{{ $offer->price }} {{ currency_display() }}</h4>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" id="submitForm">
        @csrf
    </form>
@endsection
