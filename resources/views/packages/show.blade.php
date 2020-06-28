<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="itemModalLabel">{{ $package->name }}</h3>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="modal-body">
            {!! $package->description !!}
        </div>
        <div class="modal-footer">
            <span class="flex-md-fill font-weight-bold">
                @if($package->isDiscounted())
                    <del class="small">{{ $package->getOriginalPrice() }}</del>
                @endif
                {{ shop_format_amount($package->getPrice()) }}
            </span>

            @auth
                @if(! $package->isInCart())
                    <form action="{{ route('shop.packages.buy', $package) }}" method="POST" class="form-inline">
                        @csrf

                        @if($package->has_quantity)
                            <div class="form-group">
                                <label for="quantity">{{ trans('shop::messages.fields.quantity') }}</label>
                            </div>

                            <div class="form-group mx-3">
                                <input type="number" min="0" max="{{ $package->getMaxQuantity() }}" size="5" class="form-control" name="quantity" id="quantity" value="1">
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            {{ trans('shop::messages.buy') }}
                        </button>
                    </form>
                @else
                    <form action="{{ route('shop.cart.remove', $package) }}" method="POST" class="form-inline">
                        @csrf

                        <button type="submit" class="btn btn-primary">
                            {{ trans('shop::messages.actions.remove') }}
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
