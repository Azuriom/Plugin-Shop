<div id="commands" class="mb-3" v-scope="{ shopCommands: shopCommandsList }">
    <div class="card" v-for="(command, i) in shopCommands">
        <div class="card-body">
            <div class="row gx-3">
                <div class="mb-3 col-md-6">
                    <label class="form-label" :for="'triggerSelect' + i">{{ trans('shop::admin.commands.trigger') }}</label>

                    <select class="form-select" :id="'triggerSelect' + i" :name="`commands[${i}][trigger]`" v-model="command.trigger" required>
                        @foreach($commandTriggers as $trigger)
                            <option value="{{ $trigger }}">
                                {{ trans('shop::admin.commands.triggers.'.$trigger) }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
            </div>

            <div class="row gx-3">
                <div class="col-md-8">
                    <label class="form-label" :for="'commandInput' + i">{{ trans('shop::admin.commands.command') }}</label>

                    <div class="input-group mb-3" v-for="(cmd, j) in command.commands">
                        <input type="text" class="form-control" :id="'commandInput' + i" :name="`commands[${i}][commands][${j}]`" v-model.trim="command.commands[j]" required>

                        <button type="button" v-if="j == command.commands.length - 1" class="btn btn-success" @click="command.commands.push('')" title="{{ trans('messages.actions.add') }}">
                            <i class="bi bi-plus-lg"></i>
                        </button>

                        <button type="button" v-if="command.commands.length > 1" class="btn btn-danger" @click="command.commands.splice(j, 1)" title="{{ trans('messages.actions.delete') }}">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <button type="button" v-else class="btn btn-danger" @click="shopCommands.splice(i, 1)" title="{{ trans('messages.actions.delete') }}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 col-md-4">
                    <label class="form-label" :for="'serverSelect' + i">{{ trans('messages.fields.server') }}</label>

                    <select class="form-select" :id="'serverSelect' + i" :name="`commands[${i}][server]`" v-model="command.server" required>
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

            <div class="form-text mb-3">
                @lang('shop::admin.packages.command', [
                    'placeholders' => '<code>'.implode('</code>, <code>', [
                        '{quantity}', '{package_id}', '{package_name}', '{price}', '{transaction_id}',
                    ]).'</code>',
                ])
            </div>
        </div>
    </div>

    <button type="button" @click="shopCommands.push({ commands: [''], trigger: 'purchase', require_online: 0, server: 0 })" class="btn btn-sm btn-success">
        <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
    </button>
</div>

@push('footer-scripts')
    <script>
        const shopCommandsList = @json(old('commands', $commands ?? []));
    </script>
@endpush
