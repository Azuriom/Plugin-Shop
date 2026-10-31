@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $tier->name ?? '') }}" required>

        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="amountInput">{{ trans('shop::admin.goals.amount') }}</label>

        <div class="input-group @error('amount') has-validation @enderror">
            <input type="number" min="0" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amountInput" name="amount" value="{{ old('amount', $tier->amount ?? '') }}" aria-describedby="amountInfo" required>
            <span class="input-group-text">{{ currency_display() }}</span>

            @error('amount')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div id="amountInfo" class="form-text">{{ trans('shop::admin.goals.amount_info', ['total' => $total]) }}</div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="iconInput">{{ trans('messages.fields.icon') }}</label>

    <div class="input-group @error('icon') has-validation @enderror">
        <span class="input-group-text"><i class="{{ $tier->icon ?? 'bi bi-trophy' }}"></i></span>

        <input type="text" class="form-control @error('icon') is-invalid @enderror" id="iconInput" name="icon" value="{{ old('icon', $tier->icon ?? '') }}" placeholder="bi bi-trophy" aria-labelledby="iconLabel">

        @error('icon')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <small id="iconLabel" class="form-text">@lang('messages.icons')</small>
</div>

<h2 class="h4">{{ trans('shop::admin.goals.rewards') }}</h2>

<p>{{ trans('shop::admin.goals.rewards_info') }}</p>

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="moneyInput">{{ trans('shop::admin.goals.money') }}</label>

        <div class="input-group @error('money') has-validation @enderror">
            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('money') is-invalid @enderror" id="moneyInput" name="money" value="{{ old('money', $tier->money ?? '') }}">
            <span class="input-group-text">{{ money_name() }}</span>

            @error('money')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="giftcardInput">{{ trans('shop::admin.goals.giftcard_balance') }}</label>

        <div class="input-group @error('giftcard_balance') has-validation @enderror">
            <input type="number" min="0" step="0.01" class="form-control @error('giftcard_balance') is-invalid @enderror" id="giftcardInput" name="giftcard_balance" value="{{ old('giftcard_balance', $tier->giftcard_balance ?? '') }}">
            <span class="input-group-text">{{ shop_active_currency() }}</span>

            @error('giftcard_balance')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<h3 class="h5">{{ trans('shop::messages.fields.commands') }}</h3>

@if($servers->isEmpty())
    <div class="alert alert-info" role="alert">
        <p><i class="bi bi-info-circle"></i> @lang('shop::admin.commands.servers')</p>

        <a href="{{ route('admin.servers.index') }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-hdd-network"></i> {{ trans('admin.servers.title') }}
        </a>
    </div>
@else
    @include('shop::admin.commands._form', [
        'commands' => $tier->commands ?? [],
        'commandPlaceholders' => ['{tier_id}', '{tier_name}'],
    ])
@endif
