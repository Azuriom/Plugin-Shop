<div class="list-group mb-3">
    @foreach($categories as $subCategory)
        <a href="{{ route('shop.categories.show', $subCategory) }}" class="list-group-item @if($category->is($subCategory)) active @endif">
            {{ $subCategory->name }}
        </a>

        @foreach($subCategory->categories as $cat)
            <a href="{{ route('shop.categories.show', $cat) }}" class="list-group-item pl-5 @if($category->is($cat)) active @endif">
                {{ $cat->name }}
            </a>
        @endforeach
    @endforeach
</div>

@auth
    <div class="mb-4">
        @if(use_site_money())
            <p class="text-center">{{ trans('messages.fields.money') }}: {{ format_money(auth()->user()->money) }}</p>

            <a href="{{ route('shop.offers.select') }}" class="btn btn-primary btn-block">{{ trans('shop::messages.cart.credit') }}</a>
        @endif

        <a href="{{ route('shop.cart.index') }}" class="btn btn-primary btn-block">{{ trans('shop::messages.cart.title') }}</a>
    </div>
@endauth

@if($goal !== false)
    <div class="mb-4">
        <h4>{{ trans('shop::messages.month-goal') }}</h4>

        <div class="progress mb-1">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $goal }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goal }}%"></div>
        </div>

        <p class="text-center">{{ trans_choice('shop::messages.month-goal-current', $goal) }}</p>
    </div>
@endif
