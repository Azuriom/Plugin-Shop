@extends('admin.layouts.admin')

@section('title', trans('shop::admin.packages.title'))

@push('styles')
    <style>
        #categories > .sortable-dropdown {
            padding-bottom: 1rem;
        }
    </style>
@endpush

@push('footer-scripts')
    <script src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
    <script>
        const sortable = Sortable.create(document.getElementById('categories'), {
            group: {
                name: 'packages',
                put: function (to, sortable, drag) {
                    return drag.classList.contains('category-parent');
                },
            },
            animation: 150,
            handle: '.sortable-handle'
        });

        document.querySelectorAll('.category-list').forEach(function (el) {
            Sortable.create(el, {
                group: {
                    name: 'packages',
                    put: function (to, sortable, drag) {
                        if (!drag.classList.contains('category-parent')) {
                            return true;
                        }

                        return !drag.querySelector('.category-parent .category-parent')
                            && drag.parentNode.id === 'categories';
                    },
                },
                animation: 150,
                handle: '.sortable-handle'
            });
        });

        function serializeCategory(category, preventNested = false) {
            const packagesId = [];
            const subCategories = [];
            const packages = category.querySelector('.category-list');

            [].slice.call(packages.children).forEach(function (categoryPackage) {
                if (!categoryPackage.classList.contains('category-parent')) {
                    packagesId.push(categoryPackage.dataset['packageId']);
                    return;
                }

                if (!preventNested) {
                    subCategories.push(serializeCategory(categoryPackage, true));
                }
            });

            return {
                id: category.dataset['categoryId'],
                categories: subCategories,
                packages: packagesId
            };
        }

        function serialize(categories) {
            return [].slice.call(categories.children).map(function (category) {
                return serializeCategory(category);
            });
        }

        const saveButton = document.getElementById('save');
        const saveButtonIcon = saveButton.querySelector('.btn-spinner');

        saveButton.addEventListener('click', function () {
            saveButton.setAttribute('disabled', '');
            saveButtonIcon.classList.remove('d-none');

            axios.post('{{ route('shop.admin.packages.update-order') }}', {
                'categories': serialize(sortable.el)
            })
                .then(function (json) {
                    createAlert('success', json.data.message, true);
                })
                .catch(function (error) {
                    createAlert('danger', error, true)
                })
                .finally(function () {
                    saveButton.removeAttribute('disabled');
                    saveButtonIcon.classList.add('d-none');
                });
        });
    </script>
@endpush

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">

            <ol class="list-unstyled sortable" id="categories">
                @each('shop::admin.packages._category', $categories, 'category')
            </ol>

            <a href="{{ route('shop.admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ trans('shop::admin.packages.create_category') }}
            </a>

            @if(! $categories->isEmpty())
                <a href="{{ route('shop.admin.packages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ trans('shop::admin.packages.create_package') }}
                </a>

                <button type="button" class="btn btn-success" id="save">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                    <span class="spinner-border spinner-border-sm btn-spinner d-none" role="status"></span>
                </button>
            @endif
        </div>
    </div>
@endsection
