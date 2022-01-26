@extends('layouts.app')

@section('title', $category->name)

@push('footer-scripts')
    <script>
        document.querySelectorAll('[data-package-url]').forEach(function (el) {
            el.addEventListener('click', function (ev) {
                ev.preventDefault();

                axios.get(el.dataset['packageUrl'], {
                    headers: {
                        'X-PJAX': 'true'
                    }
                }).then(function (response) {
                    $('#itemModal').html(response.data).modal('show');
                }).catch(function (error) {
                    createAlert('danger', error, true);
                });
            });
        });
    </script>
@endpush

@section('content')
    <div class="container content">
        <div class="row">
            <div class="col-lg-3">
                @include('shop::categories.sidebar')
            </div>

            <div class="col-lg-9">
                @if($category->description)
                    <div class="card mb-4">
                        <div class="card-body pb-1">
                            {!! $category->description !!}
                        </div>
                    </div>
                @endif

                <div class="row">
                    @forelse($category->packages as $package)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                @if($package->image !== null)
                                    <a href="#" data-package-url="{{ route('shop.packages.show', $package) }}">
                                        <img class="card-img-top" src="{{ $package->imageUrl() }}" alt="{{ $package->name }}">
                                    </a>
                                @endif

                                <div class="card-body">
                                    <h4 class="card-title">{{ $package->name }}</h4>
                                    <h5>
                                        @if($package->isDiscounted())
                                            <del class="small">{{ $package->getOriginalPrice() }}</del>
                                        @endif
                                        {{ shop_format_amount($package->getPrice()) }}
                                    </h5>

                                    <a href="#" class="btn btn-primary btn-block" data-package-url="{{ route('shop.packages.show', $package) }}">
                                        {{ trans('shop::messages.buy') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col">
                            <div class="alert alert-warning" role="alert">
                                {{ trans('shop::messages.categories.empty') }}
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true"></div>
@endsection
