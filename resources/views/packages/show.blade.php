<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="itemModalLabel">{{ $package->name }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            {!! $package->description !!}
        </div>
        <div class="modal-footer">
            <span class="flex-md-fill font-weight-bold">
                @if($package->isDiscounted())
                    <del class="small">{{ shop_format_amount($package->getOriginalPrice()) }}</del>
                @endif
                {{ shop_format_amount($package->getPrice()) }}
            </span>

            @auth
                @if($package->isSubscription())
                    @if($package->isUserSubscribed())
                        <a href="{{ route('shop.profile') }}" class="btn btn-primary">
                            {{ trans('shop::messages.actions.manage') }}
                        </a>
                    @else
                        <form action="{{ route('shop.subscriptions.select', $package) }}" method="POST" class="form-inline">
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                {{ trans('shop::messages.actions.subscribe') }}
                            </button>
                        </form>
                    @endif
                @elseif($package->isInCart())
                    <form action="{{ route('shop.cart.remove', $package) }}" method="POST" class="form-inline">
                        @csrf

                        <button type="submit" class="btn btn-primary">
                            {{ trans('messages.actions.remove') }}
                        </button>
                    </form>
                @elseif($package->getMaxQuantity() < 1)
                    {{ trans('shop::messages.packages.limit') }}
                @elseif(! $package->hasBoughtRequirements())
                    {{ trans('shop::messages.packages.requirements') }}
                @else
                    <form action="{{ route('shop.packages.buy', $package) }}" method="POST" class="row row-cols-lg-auto g-0 gy-2 align-items-center">
                        @csrf

                        @if($package->custom_price)
                            <label for="price">{{ trans('shop::messages.fields.price') }}</label>

                            <div class="mx-3">
                                <input type="number" min="{{ $package->getPrice() }}" size="5" class="form-control" name="price" id="price" value="{{ $package->price }}">
                            </div>
                        @endif

                        @if($package->has_quantity)
                            <label for="quantity">{{ trans('shop::messages.fields.quantity') }}</label>

                            <div class="mx-3">
                                <input type="number" min="1" max="{{ $package->getMaxQuantity() }}" size="5" class="form-control" name="quantity" id="quantity" value="1" required>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            {{ trans('shop::messages.buy') }}
                        </button>
                    </form>
                @endif
            @else
                <div class="alert alert-info" role="alert">
                    {{ trans('shop::messages.cart.guest') }}
                </div>
            @endauth
        </div>
    </div>
</div>
