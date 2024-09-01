@include('admin.elements.editor')

@push('scripts')
    <script>
        // Snippet from @thierryc on GitHub
        // https://gist.github.com/codeguy/6684588?permalink_comment_id=3243980#gistcomment-3243980
        function slugifyInput(str) {
            return str
                .normalize('NFKD')        // The normalize() using NFKD method returns the Unicode Normalization Form of a given string.
                .toLowerCase()            // Convert the string to lowercase letters
                .trim()                   // Remove whitespace from both sides of a string (optional)
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/[^\w\-]+/g, '') // Remove all non-word chars
                .replace(/--+/g, '-');    // Replace multiple - with single -
        }

        function generateSlug() {
            const name = document.getElementById('nameInput').value;

            document.getElementById('slugInput').value = slugifyInput(name);
        }
    </script>
@endpush

@csrf

<div class="row gx-3">
    <div class="mb-3 col-md-6">
        <label class="form-label" for="nameInput">{{ trans('messages.fields.name') }}</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="nameInput" name="name" value="{{ old('name', $category->name ?? '') }}" required>

        @error('name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label" for="parentSelect">{{ trans('shop::admin.categories.parent') }}</label>

        <select class="form-select" id="parentSelect" name="parent_id">
            <option value="">{{ trans('messages.none') }}</option>
            @foreach($categories as $sub)
                <option value="{{ $sub->id }}" @selected((int) old('parent_id', $category->parent_id ?? 0) === $sub->id)>
                    {{ $sub->name }}
                </option>
            @endforeach
        </select>

        @error('category_id')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="iconInput">{{ trans('messages.fields.icon') }}</label>

    <div class="input-group @error('icon') has-validation @enderror">
        <span class="input-group-text"><i class="{{ $category->icon ?? 'bi bi-book' }} fa-fw"></i></span>

        <input type="text" class="form-control @error('icon') is-invalid @enderror" id="iconInput" name="icon" value="{{ old('icon', $category->icon ?? '') }}" placeholder="bi bi-book" aria-labelledby="iconLabel">

        @error('icon')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <small id="iconLabel" class="form-text">@lang('messages.icons')</small>
</div>

<div class="mb-3">
    <label class="form-label" for="slugInput">{{ trans('messages.fields.slug') }}</label>
    <div class="input-group @error('slug') has-validation @enderror">
        <div class="input-group-text">{{ route('shop.categories.show', '') }}/</div>
        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slugInput" name="slug" value="{{ old('slug', $category->slug ?? '') }}" required>

        <button type="button" class="btn btn-outline-secondary" onclick="generateSlug()">
            <i class="bi bi-arrow-clockwise"></i>
        </button>

        @error('slug')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="descriptionArea">{{ trans('messages.fields.description') }}</label>
    <textarea class="form-control html-editor @error('description') is-invalid @enderror" id="descriptionArea" name="description" rows="5">{{ old('description', $category->description ?? '') }}</textarea>

    @error('description')
    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" class="form-check-input" id="cumulatePurchasesSwitch" name="cumulate_purchases" @checked(old('cumulate_purchases', $category->cumulate_purchases ?? false)) aria-describedby="cumulateInfo">
        <label class="form-check-label" for="cumulatePurchasesSwitch">{{ trans('shop::admin.categories.cumulate') }}</label>
    </div>

    <div class="form-text" id="cumulateInfo">
        {{ trans('shop::admin.categories.cumulate_info') }}
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="singleSwitch" name="single_purchase" @checked(old('single_purchase', $category->single_purchase ?? false))>
    <label class="form-check-label" for="singleSwitch">{{ trans('shop::admin.categories.single_purchase') }}</label>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" class="form-check-input" id="enableSwitch" name="is_enabled" @checked(old('is_enabled', $category->is_enabled ?? true))>
    <label class="form-check-label" for="enableSwitch">{{ trans('shop::admin.categories.enable') }}</label>
</div>
