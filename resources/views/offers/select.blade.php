@extends('layouts.app')

@section('title', trans('shop::messages.offers.amount'))

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
    <h1>{{ trans('shop::messages.offers.amount') }}</h1>

    <div class="row gy-3">
        @forelse($offers as $offer)
            <div class="col-md-3">
                <div class="card">
                    <a href="{{ route('shop.offers.pay', [$offer->id, $gateway->type]) }}" class="payment-method">
                        <div class="card-body text-center">
                            @if($offer->hasImage())
                                <img src="{{ $offer->imageUrl() }}" alt="{{ $offer->name }}" class="img-fluid">
                            @endif
                            <h3>{{ $offer->name }}</h3>
                            <h4>{{ $offer->price }} {{ currency_display() }}</h4>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col">
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ trans('shop::messages.offers.empty') }}
                </div>
            </div>
        @endforelse
    </div>

    <form method="POST" id="submitForm">
        @csrf
    </form>
@endsection
