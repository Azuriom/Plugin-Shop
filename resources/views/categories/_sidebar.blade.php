<div class="list-group mb-4">
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

@if($shopUser !== null)
    <div class="d-grid gap-2 mb-4">
        <div class="d-flex justify-content-center mb-3">
            <div class="flex-shrink-0 d-flex align-items-center">
                <img src="{{ $shopUser->getAvatar(48) }}" class="me-3 rounded" alt="{{ $shopUser->name }}" width="48">
            </div>
            <div class="align-self-center">
                <h3 class="mb-0">{{ $shopUser->name }}</h3>
                @if(use_site_money())
                    <p class="mb-0">
                        {{ trans('shop::messages.profile.money', ['balance' => format_money($shopUser->money)]) }}
                    </p>
                @endif
            </div>
        </div>

        @if(use_site_money())
            <a href="{{ route('shop.offers.select') }}" class="btn btn-primary">
                <i class="bi bi-credit-card"></i> {{ trans('shop::messages.cart.credit') }}
            </a>
        @endif

        <a href="{{ route('shop.cart.index') }}" class="btn btn-primary">
            <i class="bi bi-cart"></i> {{ trans('shop::messages.cart.title') }}
        </a>

        @if($userHasPayments)
            <a href="{{ route('shop.profile') }}" class="btn btn-primary">
                <i class="bi bi-cash-coin"></i> {{ trans('shop::messages.profile.payments') }}
            </a>
        @endif

        @guest
            <form action="{{ route('shop.logout') }}" method="POST" class="text-center">
                @csrf
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-box-arrow-right"></i> {{ trans('auth.logout') }}
                </button>
            </form>
        @endguest
    </div>
@else
    <a href="{{ route('shop.login') }}" class="btn btn-primary d-block mb-4">
        <i class="bi bi-box-arrow-in-right"></i> {{ trans('auth.login') }}
    </a>
@endif

@if(! $goalTiers->isEmpty())
    @php($nextTier = $goalTiers->first(fn ($tier) => ! $tier->isReached()))
    @php($progress = $nextTier?->progress($goalTotal) ?? 100)

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-graph-up"></i> {{ trans('shop::messages.goal.title') }}</span>

            @if($goalTiers->count() > 1)
                <span class="badge bg-primary rounded-pill">
                    {{ $goalTiers->filter->isReached()->count() }}/{{ $goalTiers->count() }}
                </span>
            @endif
        </div>
        <div class="card-body">
            <div class="progress mb-1">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%"></div>
            </div>

            <p class="card-text text-center">
                @if($nextTier === null)
                    {{ trans('shop::messages.goal.completed') }}
                @elseif($displayGoalAmount)
                    {{ trans('shop::messages.goal.remaining', ['amount' => $nextTier->formatRemaining($goalTotal), 'tier' => $nextTier->name]) }}
                @else
                    {{ trans('shop::messages.goal.progress_tier', ['progress' => $progress, 'tier' => $nextTier->name]) }}
                @endif
            </p>
        </div>

        <ul class="list-group list-group-flush">
            @foreach($goalTiers as $tier)
                <li class="list-group-item d-flex align-items-center">
                    @if($tier->icon)
                        <i class="{{ $tier->icon }} me-2"></i>
                    @endif

                    <div class="flex-grow-1">
                        <span class="fw-bold">{{ $tier->name }}</span>

                        @if($displayGoalAmount)
                            <div class="small">{{ $tier->formatAmount() }}</div>
                        @endif
                    </div>

                    @if($tier->isReached())
                        <i class="bi bi-check-circle-fill text-success" title="{{ trans('shop::messages.goal.reached') }}"></i>
                    @else
                        <i class="bi bi-lock-fill text-body-secondary"></i>
                    @endif
                </li>
            @endforeach
        </ul>
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
                    {{ $topCustomer->formatPrice() }}
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
                                {{ $payment->formatPrice() }}
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
