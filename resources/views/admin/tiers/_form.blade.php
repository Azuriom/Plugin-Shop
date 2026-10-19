@csrf

<div v-scope="{
    type: '{{ old('type', $tier->type ?? 'commands') }}',
    commandsList: initialTierCommands
}">
    <div class="row gx-3">
        <div class="mb-3 col-md-6">
            <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $tier->name ?? '') }}" required>

            @error('name')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label" for="minSpendInput">{{ trans('shop::admin.tiers.min_spend') }}</label>
            <div class="input-group @error('min_spend') has-validation @enderror">
                <input type="number" step="0.01" min="0" class="form-control @error('min_spend') is-invalid @enderror" id="minSpendInput" name="min_spend" value="{{ old('min_spend', $tier->min_spend ?? '10.00') }}" required>
                <span class="input-group-text">{{ shop_active_currency() }}</span>

                @error('min_spend')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            <small class="form-text">{{ trans('shop::admin.tiers.min_spend_info') }}</small>
        </div>
    </div>

    <div class="row gx-3">
        <div class="mb-3 col-md-6">
            <label class="form-label" for="iconInput">{{ trans('shop::admin.tiers.icon') }}</label>

            <div class="input-group @error('icon') has-validation @enderror">
                <span class="input-group-text" id="iconPreview">
                    @if(isset($tier) && $tier->isBootstrapIcon())
                        <i class="{{ $tier->icon }}"></i>
                    @elseif(isset($tier) && $tier->icon)
                        <span>{{ $tier->icon }}</span>
                    @else
                        <i class="bi bi-award"></i>
                    @endif
                </span>

                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="iconInput" name="icon" value="{{ old('icon', $tier->icon ?? 'bi bi-award') }}" placeholder="bi bi-award ou 🔑" aria-labelledby="iconLabel">

                @error('icon')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <small id="iconLabel" class="form-text">@lang('shop::admin.tiers.icon_info')</small>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label" for="typeSelect">{{ trans('shop::admin.tiers.reward_type') }}</label>
            <select class="form-select @error('type') is-invalid @enderror" id="typeSelect" name="type" v-model="type" required>
                <option value="commands">{{ trans('shop::admin.tiers.types.commands') }}</option>
                <option value="coupon">{{ trans('shop::admin.tiers.types.coupon') }}</option>
                <option value="giftcard">{{ trans('shop::admin.tiers.types.giftcard') }}</option>
                <option value="money">{{ trans('shop::admin.tiers.types.money') }}</option>
            </select>

            @error('type')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>

    <div v-if="type === 'commands'" class="mb-3">
        <h2 class="h4">{{ trans('shop::messages.fields.commands') }}</h2>

        @if($servers->isEmpty())
            <div class="alert alert-info" role="alert">
                <p><i class="bi bi-info-circle"></i> @lang('shop::admin.commands.servers')</p>

                <a href="{{ route('admin.servers.index') }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-hdd-network"></i> {{ trans('admin.servers.title') }}
                </a>
            </div>
        @else
            <div class="card mb-3" v-for="(command, i) in commandsList">
                <div class="card-body">
                    <div class="row gx-3">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" :for="'serverSelect' + i">{{ trans('messages.fields.server') }}</label>
                            <select class="form-select" :id="'serverSelect' + i" :name="`commands[${i}][server]`" v-model="command.server" required>
                                @foreach($servers as $serverId => $name)
                                    <option value="{{ $serverId }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label" :for="'onlineCheck' + i">{{ trans('shop::admin.commands.condition') }}</label>
                            <select class="form-select" :id="'onlineCheck' + i" :name="`commands[${i}][require_online]`" v-model="command.require_online" required>
                                <option value="0">{{ trans('shop::admin.commands.offline') }}</option>
                                <option value="1">{{ trans('shop::admin.commands.online') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('shop::admin.commands.command') }}</label>
                        <div class="input-group mb-2" v-for="(cmd, j) in command.commands">
                            <input type="text" class="form-control" :name="`commands[${i}][commands][${j}]`" v-model.trim="command.commands[j]" placeholder="give {player} diamond 64" required>

                            <button type="button" v-if="j == command.commands.length - 1" class="btn btn-success" @click="command.commands.push('')" title="{{ trans('messages.actions.add') }}">
                                <i class="bi bi-plus-lg"></i>
                            </button>

                            <button type="button" v-if="command.commands.length > 1" class="btn btn-danger" @click="command.commands.splice(j, 1)" title="{{ trans('messages.actions.delete') }}">
                                <i class="bi bi-x-lg"></i>
                            </button>

                            <button type="button" v-else class="btn btn-danger" @click="commandsList.splice(i, 1)" title="{{ trans('messages.actions.delete') }}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" @click="commandsList.push({ server: {{ $servers->keys()->first() ?? 0 }}, require_online: 0, commands: [''] })" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </button>

            <div class="form-text mt-2">
                @lang('shop::admin.tiers.commands.variables_info', [
                    'variables' => '<code>{player}</code>, <code>{tier_name}</code>, <code>{min_spend}</code>'
                ])
            </div>
        @endif
    </div>

    <div v-if="type === 'coupon'" class="card card-body mb-3">
        <h2 class="h5 mb-3">{{ trans('shop::admin.tiers.types.coupon') }}</h2>

        <div class="row gx-3">
            <div class="mb-3 col-md-6">
                <label class="form-label" for="discountInput">{{ trans('shop::admin.tiers.coupon.discount') }}</label>
                <div class="input-group">
                    <input type="number" min="0" step="0.01" class="form-control @error('reward_data.discount') is-invalid @enderror" id="discountInput" name="reward_data[discount]" value="{{ old('reward_data.discount', $tier->reward_data['discount'] ?? '10') }}" required>
                    <div class="input-group-append">
                        <select class="form-select" name="reward_data[is_fixed]">
                            <option value="0" @selected(!old('reward_data.is_fixed', $tier->reward_data['is_fixed'] ?? false))>%</option>
                            <option value="1" @selected(old('reward_data.is_fixed', $tier->reward_data['is_fixed'] ?? false))>{{ shop_active_currency() }}</option>
                        </select>
                    </div>

                    @error('reward_data.discount')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            <div class="mb-3 col-md-6">
                <label class="form-label" for="couponDurationInput">{{ trans('shop::admin.tiers.coupon.duration') }}</label>
                <input type="number" min="1" class="form-control @error('reward_data.duration_days') is-invalid @enderror" id="couponDurationInput" name="reward_data[duration_days]" value="{{ old('reward_data.duration_days', $tier->reward_data['duration_days'] ?? '') }}" placeholder="30">
                <small class="form-text">{{ trans('shop::admin.tiers.coupon.duration_info') }}</small>

                @error('reward_data.duration_days')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div v-if="type === 'giftcard'" class="card card-body mb-3">
        <h2 class="h5 mb-3">{{ trans('shop::admin.tiers.types.giftcard') }}</h2>

        <div class="mb-0">
            <label class="form-label" for="giftcardBalanceInput">{{ trans('shop::admin.tiers.giftcard.balance') }}</label>
            <div class="input-group @error('reward_data.balance') has-validation @enderror">
                <input type="number" min="0.01" step="0.01" class="form-control @error('reward_data.balance') is-invalid @enderror" id="giftcardBalanceInput" name="reward_data[balance]" value="{{ old('reward_data.balance', $tier->reward_data['balance'] ?? '5.00') }}" required>
                <span class="input-group-text">{{ shop_active_currency() }}</span>

                @error('reward_data.balance')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div v-if="type === 'money'" class="card card-body mb-3">
        <h2 class="h5 mb-3">{{ trans('shop::admin.tiers.types.money') }}</h2>

        <div class="mb-0">
            <label class="form-label" for="moneyAmountInput">{{ trans('shop::admin.tiers.money.amount') }}</label>
            <div class="input-group @error('reward_data.amount') has-validation @enderror">
                <input type="number" min="0.01" step="0.01" class="form-control @error('reward_data.amount') is-invalid @enderror" id="moneyAmountInput" name="reward_data[amount]" value="{{ old('reward_data.amount', $tier->reward_data['amount'] ?? '50') }}" required>
                <span class="input-group-text">{{ money_name() }}</span>

                @error('reward_data.amount')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>

    <div class="mb-3 form-check form-switch">
        <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" value="1" @checked(old('is_enabled', $tier->is_enabled ?? true))>
        <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.tiers.enable') }}</label>
    </div>
</div>

@push('styles')
    <style>
        .input-group-append .form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
@endpush

@push('footer-scripts')
    <script>
        const initialTierCommands = @json($commands);

        const iconInput = document.getElementById('iconInput');
        if (iconInput) {
            iconInput.addEventListener('input', function () {
                const preview = document.getElementById('iconPreview');
                const val = this.value.trim();
                if (!val) {
                    preview.innerHTML = '<i class="bi bi-award"></i>';
                } else if (val.startsWith('bi ') || val.startsWith('bi-') || val.startsWith('fa ') || val.startsWith('fas ')) {
                    preview.innerHTML = '<i class="' + val + '"></i>';
                } else {
                    preview.innerHTML = '<span>' + val + '</span>';
                }
            });
        }
    </script>
@endpush
