<div class="list-group mb-3">
    @if($displayHome)
        <a href="{{ route('shop.home') }}" class="list-group-item @if($category === null) active @endif">
            {{ trans('messages.home') }}
        </a>
    @endif

    @foreach($categories as $subCategory)
        <a href="{{ route('shop.categories.show', $subCategory) }}" class="list-group-item @if($subCategory->is($category)) active @endif">
            @if($subCategory->icon)
                <i class="{{ $subCategory->icon }}"></i>
            @endif
            {{ $subCategory->name }}
        </a>

        @foreach($subCategory->categories as $cat)
            <a href="{{ route('shop.categories.show', $cat) }}" class="list-group-item ps-5 @if($cat->is($category)) active @endif">
                @if($cat->icon)
                    <i class="{{ $cat->icon }}"></i>
                @endif
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
                <i class="bi bi-credit-card"></i> {{ trans('shop::messages.cart.credit') }}
            </a>
        @endif

        <a href="{{ route('shop.cart.index') }}" class="btn btn-primary btn-block">
            <i class="bi bi-cart"></i> {{ trans('shop::messages.cart.title') }}
        </a>

        <a href="{{ route('shop.profile') }}" class="btn btn-primary btn-block">
            <i class="bi bi-cash-coin"></i> {{ trans('shop::messages.profile.payments') }}
        </a>
    </div>
@endauth

@if($goal > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-graph-up"></i> {{ trans('shop::messages.goal.title') }}
        </div>
        <div class="card-body">
            <div class="progress mb-1">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $goal }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ min($goal, 100) }}%"></div>
            </div>

            <p class="card-text text-center">
                {{ trans_choice('shop::messages.goal.progress', $goal) }}
            </p>
        </div>
    </div>
@endif

@if($topCustomer !== null)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-star"></i> {{ trans('shop::messages.top.title') }}
        </div>
        <div class="card-body d-flex">
            <div class="flex-shrink-0">
                <img class="me-3 rounded" src="{{ $topCustomer->user->getAvatar(64) }}" alt="{{ $topCustomer->user->name }}" width="64">
            </div>
            <div class="flex-grow-1">
                <p class="h4 mb-1">{{ $topCustomer->user->name }}</p>
                @if($displaySidebarAmount)
                    {{ $topCustomer->total.' '.currency_display() }}
                @endif
            </div>
        </div>
    </div>
@endif

@if($recentPayments !== null)
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-list-check"></i> {{ trans('shop::messages.recent.title') }}
        </div>
        <div class="list-group list-group-flush">
            @forelse($recentPayments as $payment)
                <div class="list-group-item d-flex">
                    <div class="flex-shrink-0 d-flex align-items-center">
                        <img src="{{ $payment->user->getAvatar(48) }}" class="me-3 rounded" alt="{{ $payment->user->name }}" width="32">
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-1">{{ $payment->user->name }}</p>
                        <small>
                            @if($displaySidebarAmount)
                                {{ $payment->price.' '.currency_display() }} -
                            @endif
                            {{ format_date($payment->created_at) }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="list-group-item">
                    {{ trans('shop::messages.recent.empty') }}
                </div>
            @endforelse
        </div>
    </div>
@endif
