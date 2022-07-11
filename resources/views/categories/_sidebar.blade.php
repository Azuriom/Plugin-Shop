<div class="list-group mb-3">
    @if($displayHome)
        <a href="{{ route('shop.home') }}" class="list-group-item @if($category === null) active @endif">
            {{ trans('messages.home') }}
        </a>
    @endif

    @foreach($categories as $subCategory)
        <a href="{{ route('shop.categories.show', $subCategory) }}" class="list-group-item @if($subCategory->is($category)) active @endif">
            {{ $subCategory->name }}
        </a>

        @foreach($subCategory->categories as $cat)
            <a href="{{ route('shop.categories.show', $cat) }}" class="list-group-item ps-5 @if($cat->is($category)) active @endif">
                {{ $cat->name }}
            </a>
        @endforeach
    @endforeach
</div>

@auth
    <div class="d-grid gap-2 mb-4">
        @if(use_site_money())
            <p class="text-center mb-0">
                {{ trans('shop::messages.profile.money', ['balance' => format_money(auth()->user()->money)]) }}
            </p>

            <a href="{{ route('shop.offers.select') }}" class="btn btn-primary btn-block">
                {{ trans('shop::messages.cart.credit') }}
            </a>
        @endif

        <a href="{{ route('shop.cart.index') }}" class="btn btn-primary btn-block">
            {{ trans('shop::messages.cart.title') }}
        </a>
    </div>
@endauth

@if($goal !== false)
    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ trans('shop::messages.goal.title') }}</h4>

            <div class="progress mb-1">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $goal }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ min($goal, 100) }}%"></div>
            </div>

            <p class="card-text text-center">
                {{ trans_choice('shop::messages.goal.progress', $goal) }}
            </p>
        </div>
    </div>
@endif
