<div id="commands" class="mb-3" v-scope="{ shopCommands: shopCommandsList }">
    <div class="card" v-for="(command, i) in shopCommands">
        <div class="card-body">
            <div class="row g-3">
                <div class="mb-3 col-md-4">
                    <label class="form-label" :for="'triggerSelect' + i">{{ trans('shop::admin.commands.trigger') }}</label>

                    <select class="form-select" :id="'triggerSelect' + i" :name="`commands[${i}][trigger]`" v-model="command.trigger" required>
                        @foreach($commandTriggers as $trigger)
                            <option value="{{ $trigger }}">
                                {{ trans('shop::admin.commands.triggers.'.$trigger) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 col-md-8">
                    <label class="form-label" :for="'commandInput' + i">{{ trans('shop::admin.commands.command') }}</label>

                    <div class="input-group">
                        <input type="text" class="form-control" :id="'commandInput' + i" :name="`commands[${i}][command]`" v-model="command.command" required>

                        <button type="button" class="btn btn-danger" @click="shopCommands.splice(i, 1)" title="{{ trans('messages.actions.delete') }}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <small class="form-text mb-3">@lang('shop::admin.packages.command', [
                'placeholders' => '<code>'.implode('</code>, <code>', [
                    '{quantity}', '{package_id}', '{package_name}', '{price}', '{transaction_id}',
                ]).'</code>',
            ])</small>

            <div class="row g-3">
                <div class="mb-3 col-md-6">
                    <label class="form-label" :for="'onlineCheck' + i">{{ trans('shop::admin.commands.condition') }}</label>

                    <select class="form-select" :id="'onlineCheck' + i" :name="`commands[${i}][require_online]`" v-model="command.require_online" required>
                        <option value="0">
                            {{ trans('shop::admin.commands.offline') }}
                        </option>
                        <option value="1">
                            {{ trans('shop::admin.commands.online') }}
                        </option>
                    </select>

                    @error('servers')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label" for="'serverSelect' + i">{{ trans('shop::messages.fields.server') }}</label>

                    <select class="form-select" id="'serverSelect' + i" :name="`commands[${i}][server]`" v-model="command.server" required>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}">
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('servers')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <button type="button" @click="shopCommands.push({ command: '', trigger: 'purchase', require_online: 0, server: 0 })" class="btn btn-sm btn-success">
        <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
    </button>
</div>

@push('footer-scripts')
    <script>
        const shopCommandsList = @json(old('commands', $commands ?? []));
    </script>
@endpush
