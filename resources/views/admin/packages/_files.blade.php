<div v-scope="{ files: shopFiles }">
    <label class="form-label" for="fileInput">{{ trans('shop::admin.packages.files') }}</label>

    <div v-for="(name, file, i) in files">
        <div class="mb-1">
            <div class="input-group">
                <input type="text" class="form-control" :name="`files[${file}]`" v-model.trim="name" required>

                <button type="button" class="btn btn-danger" @click="delete files[file]" title="{{ trans('messages.actions.delete') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <input type="file" class="form-control @error('file') is-invalid @enderror" id="fileInput" name="file">

        @error('file')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

@push('footer-scripts')
    <script>
        const shopFiles = @json(old('files', $package->files ?? []));
    </script>
@endpush
