<li class="sortable-item sortable-dropdown category-parent" data-category-id="{{ $category->id }}">
    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <span>
                <i class="fas fa-arrows-alt sortable-handle"></i>
                <a href="{{ route('shop.categories.show', $category) }}">{{ $category->name }}</a>
                <i class="fas fa-th"></i>
            </span>
            <span>
                <a href="{{ route('shop.admin.categories.edit', $category) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-toggle="tooltip"><i class="fas fa-edit"></i></a>
                <a href="{{ route('shop.admin.categories.destroy', $category) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
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
                            <i class="fas fa-arrows-alt sortable-handle"></i>
                            {{ $package->name }}
                        </span>
                        <div class="d-inline-block">
                            <a href="{{ route('shop.admin.packages.edit', $package) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-toggle="tooltip"><i class="fas fa-edit"></i></a>
                            <form class="d-inline-block" action="{{ route('shop.admin.packages.duplicate', $package) }}" method="POST">
                                @csrf
                                <button class="btn btn-link mx-1 p-0" title="{{ trans('shop::messages.actions.duplicate') }}" data-toggle="tooltip"><i class="fas fa-clone"></i></button>
                            </form>
                            <a href="{{ route('shop.admin.packages.destroy', $package) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</li>
