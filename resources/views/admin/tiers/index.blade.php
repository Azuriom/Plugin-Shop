@extends('admin.layouts.admin')

@section('title', trans('shop::admin.tiers.title'))

@push('footer-scripts')
    <script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
    <script>
        const sortable = Sortable.create(document.getElementById('tiers'), {
            animation: 150,
            handle: '.sortable-handle'
        });

        function serialize(table) {
            return [].slice.call(table.children).map(function (child) {
                return child.dataset['id'];
            });
        }

        const saveButton = document.getElementById('save');
        const saveButtonIcon = saveButton ? saveButton.querySelector('.btn-spinner') : null;

        if (saveButton) {
            saveButton.addEventListener('click', function () {
                saveButton.setAttribute('disabled', '');
                if (saveButtonIcon) {
                    saveButtonIcon.classList.remove('d-none');
                }

                axios.post('{{ route('shop.admin.tiers.positions') }}', {
                    'tiers': serialize(sortable.el)
                }).then(function (json) {
                    createAlert('success', json.data.message, true);
                }).catch(function (error) {
                    createAlert('danger', error, true);
                }).finally(function () {
                    saveButton.removeAttribute('disabled');
                    if (saveButtonIcon) {
                        saveButtonIcon.classList.add('d-none');
                    }
                });
            });
        }
    </script>
@endpush

@section('content')
    @if($lastReset)
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            {{ trans('shop::admin.tiers.last_reset', ['date' => format_date($lastReset, true)]) }}
            @if($unlockedCount > 0)
                &bull; {{ trans_choice('shop::admin.tiers.unlocked_users_count', $unlockedCount, ['count' => $unlockedCount]) }}
            @endif
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col" style="width: 20px"></th>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('shop::admin.tiers.min_spend') }}</th>
                        <th scope="col">{{ trans('shop::admin.tiers.reward_type') }}</th>
                        <th scope="col">{{ trans('messages.fields.enabled') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody id="tiers">
                    @foreach($tiers as $tier)
                        <tr data-id="{{ $tier->id }}">
                            <td>
                                <i class="bi bi-arrows-move sortable-handle" style="cursor: move;"></i>
                            </td>
                            <th scope="row">{{ $tier->id }}</th>
                            <td>
                                @if($tier->isBootstrapIcon())
                                    <i class="{{ $tier->icon }} me-1"></i>
                                @elseif($tier->icon)
                                    <span class="me-1">{{ $tier->icon }}</span>
                                @endif
                                {{ $tier->name }}
                            </td>
                            <td>{{ shop_format_amount($tier->min_spend) }}</td>
                            <td>
                                @if($tier->type === 'commands')
                                    <span class="badge bg-info">
                                        {{ trans('shop::admin.tiers.types.commands') }}
                                    </span>
                                @elseif($tier->type === 'coupon')
                                    <span class="badge bg-warning">
                                        {{ trans('shop::admin.tiers.types.coupon') }}
                                        ({{ $tier->reward_data['is_fixed'] ?? false ? shop_format_amount($tier->reward_data['discount'] ?? 0) : ($tier->reward_data['discount'] ?? 0).' %' }})
                                    </span>
                                @elseif($tier->type === 'giftcard')
                                    <span class="badge bg-success">
                                        {{ trans('shop::admin.tiers.types.giftcard') }}
                                        ({{ shop_format_amount($tier->reward_data['balance'] ?? 0) }})
                                    </span>
                                @elseif($tier->type === 'money')
                                    <span class="badge bg-secondary">
                                        {{ trans('shop::admin.tiers.types.money') }}
                                        ({{ format_money($tier->reward_data['amount'] ?? 0) }})
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tier->is_enabled ? 'success' : 'danger' }}">
                                    {{ trans_bool($tier->is_enabled) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('shop.admin.tiers.edit', $tier) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('shop.admin.tiers.destroy', $tier) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.tiers.create') }}">
                <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
            </a>

            @if($tiers->count() > 1)
                <button type="button" class="btn btn-success" id="save">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                    <span class="spinner-border spinner-border-sm btn-spinner d-none" role="status"></span>
                </button>
            @endif

            @if(! $tiers->isEmpty())
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#resetTiersModal">
                    <i class="bi bi-arrow-counterclockwise"></i> {{ trans('shop::admin.tiers.reset') }}
                </button>
            @endif
        </div>
    </div>

    <div class="modal fade" id="resetTiersModal" tabindex="-1" aria-labelledby="resetTiersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('shop.admin.tiers.reset') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetTiersModalLabel">
                            {{ trans('shop::admin.tiers.reset_title') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ trans('shop::admin.tiers.reset_confirm') }}</p>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i> {{ trans('shop::admin.tiers.reset_warning') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('messages.actions.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-arrow-counterclockwise"></i> {{ trans('shop::admin.tiers.reset_confirm_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

