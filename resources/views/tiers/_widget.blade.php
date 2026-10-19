@if($tierProgress !== null && ! $tierProgress['tiers']->isEmpty())
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-gift"></i> {{ trans('shop::messages.tiers.title') }}
            </span>
            <span class="badge bg-primary rounded-pill">
                {{ $tierProgress['unlockedCount'] }}/{{ $tierProgress['totalCount'] }}
            </span>
        </div>

        <div class="card-body">
            <div class="progress mb-2" role="progressbar" aria-valuenow="{{ $tierProgress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-striped progress-bar-animated @if($tierProgress['unlockedCount'] >= $tierProgress['totalCount']) bg-success @endif"
                     style="width: {{ $tierProgress['percentage'] }}%"></div>
            </div>

            <p class="card-text text-center small mb-0">
                @if($tierProgress['nextTier'] !== null)
                    {!! trans('shop::messages.tiers.remaining', [
                        'amount' => shop_format_amount($tierProgress['remainingToNext']),
                        'tier' => trim($tierProgress['nextTier']->iconHtml().' '.e($tierProgress['nextTier']->name)),
                    ]) !!}
                @else
                    <span class="text-success fw-bold">
                        <i class="bi bi-trophy-fill text-warning me-1"></i>
                        {{ trans('shop::messages.tiers.all_unlocked') }}
                    </span>
                @endif
            </p>
        </div>

        <div class="list-group list-group-flush">
            @foreach($tierProgress['tiers'] as $tier)
                @php
                    $isUnlocked = in_array($tier->id, $tierProgress['unlockedIds'], true);
                    $userReward = $tierProgress['unlockedDetails'][$tier->id] ?? null;
                @endphp
                <div class="list-group-item d-flex justify-content-between align-items-center @if($isUnlocked) list-group-item-success @endif">
                    <div class="d-flex align-items-center me-2">
                        {!! $tier->iconHtml('fs-5 me-2') !!}

                        <div>
                            <div class="fw-semibold">{{ $tier->name }}</div>
                            <small class="text-muted">{{ shop_format_amount($tier->min_spend) }}</small>

                            @if($isUnlocked && $userReward && isset($userReward['code']))
                                <div class="mt-1">
                                    <span class="badge bg-secondary user-select-all"
                                          role="button"
                                          title="{{ trans('messages.actions.copy') }}"
                                          onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $userReward['code'] }}');">
                                        <i class="bi bi-clipboard me-1"></i>{{ $userReward['code'] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="text-end text-nowrap">
                        @if($isUnlocked)
                            <span class="badge bg-success rounded-pill" title="{{ trans('shop::messages.tiers.unlocked_status') }}">
                                <i class="bi bi-check-lg"></i>
                            </span>
                        @else
                            <span class="text-muted" title="{{ trans('shop::messages.tiers.locked_status') }}">
                                <i class="bi bi-lock-fill fs-5"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @guest
            <div class="card-footer text-center py-2">
                <small>
                    <a href="{{ route('shop.login') }}" class="text-decoration-none">
                        <i class="bi bi-box-arrow-in-right me-1"></i>{{ trans('shop::messages.tiers.login_prompt') }}
                    </a>
                </small>
            </div>
        @endguest
    </div>
@endif
