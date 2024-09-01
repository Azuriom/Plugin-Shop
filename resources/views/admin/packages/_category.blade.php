<li class="sortable-item sortable-dropdown category-parent" data-category-id="{{ $category->id }}">
    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <span>
                <i class="bi bi-arrows-move sortable-handle"></i>
                <a href="{{ route('shop.categories.show', $category) }}">
                    @if($category->icon)
                        <i class="{{ $category->icon }}"></i>
                    @endif
                    {{ $category->name }}
                </a>
                <i class="bi bi-collection"></i>
            </span>
            <span>
                <a href="{{ route('shop.admin.categories.edit', $category) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                <a href="{{ route('shop.admin.categories.destroy', $category) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
            </span>
        </div>
    </div>
    <ol class="list-unstyled sortable sortable-list category-list">
        @each('shop::admin.packages._category', $category->categories, 'category')

        @foreach($category->packages as $package)
            <li class="sortable-item" data-package-id="{{ $package->id }}">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <span>
                            <i class="bi bi-arrows-move sortable-handle"></i>
                            {{ $package->name }}
                        </span>
                        <div class="d-inline-block">
                            <a href="{{ route('shop.admin.packages.edit', $package) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-bs-toggle="tooltip"><i class="bi bi-pencil-square"></i></a>
                            <form class="d-inline-block" action="{{ route('shop.admin.packages.duplicate', $package) }}" method="POST">
                                @csrf
                                <button class="btn btn-link mx-1 p-0" title="{{ trans('messages.actions.duplicate') }}" data-bs-toggle="tooltip"><i class="bi bi-layers"></i></button>
                            </form>
                            <a href="{{ route('shop.admin.packages.destroy', $package) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-bs-toggle="tooltip" data-confirm="delete"><i class="bi bi-trash"></i></a>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</li>
