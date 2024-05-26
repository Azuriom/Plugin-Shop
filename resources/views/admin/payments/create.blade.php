@extends('admin.layouts.admin')

@section('title', trans('shop::admin.payments.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('shop.admin.payments.store') }}" method="POST">
                @include('shop::admin.elements.select')

                @csrf

                <div class="row gx-3">
                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="userInput">{{ trans('shop::messages.fields.user_id') }}</label>
                        <input type="number" class="form-control @error('user_id') is-invalid @enderror" id="userInput" name="user_id" value="{{ old('user_id') }}" required>

                        @error('user_id')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="transactionInput">{{ trans('shop::messages.fields.payment_id') }}</label>

                        <div class="input-group @error('transaction_id') has-validation @enderror">
                            <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" id="transactionInput" name="transaction_id" value="{{ old('transaction_id') }}" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPaymentId()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>

                            @error('transaction_id')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label" for="priceInput">{{ trans('shop::messages.fields.price') }}</label>

                        <div class="input-group @error('price') has-validation @enderror">
                            <input type="number" min="0" step="0.01" max="999999" class="form-control @error('price') is-invalid @enderror" id="priceInput" name="price" value="{{ old('price', 0) }}" required>
                            <span class="input-group-text">{{ shop_active_currency() }}</span>

                            @error('price')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <label class="form-label">{{ trans('shop::messages.fields.packages') }}</label>

                <div v-scope="{ paymentPackages: shopPaymentPackages, quantifiablePackages: packagesWithQuantities }" class="mb-3">
                    <div v-for="(package, i) in paymentPackages" class="mb-3 input-group">
                        <select class="form-select" :name="`packages[${i}][id]`" v-model.number="package.id" aria-label="{{ trans('messages.fields.name') }}" required>
                            @foreach($categories as $category)
                                <optgroup label="{{ $category->name }}">
                                    @foreach($category->packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <input v-if="quantifiablePackages.includes(package.id)" type="number" min="1" class="form-control" placeholder="{{ trans('shop::messages.fields.quantity') }}"
                               title="{{ trans('shop::messages.fields.quantity') }}" v-model.number="package.quantity" :name="`packages[${i}][quantity]`">

                        <input v-else type="number" class="form-control" title="{{ trans('shop::messages.fields.quantity') }}" value="1" disabled>

                        <button type="button" @click="paymentPackages.splice(i, 1)" class="btn btn-sm btn-danger"
                                title="{{ trans('messages.actions.delete') }}" :disabled="i === 0">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <button type="button" @click="paymentPackages.push({ id: '', quantity: 1 })" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> {{ trans('messages.actions.add') }}
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>
            </form>
        </div>
    </div>
@endsection

@push('footer-scripts')
    <script>
        const shopPaymentPackages = @json(old('packages', [['id' => 0, 'quantity' => 1]]));
        const packagesWithQuantities = @json($quantifiablePackages);

        function generateRandomPaymentId() {
            const randomId = Math.random().toString(36).substring(2, 15)
            document.getElementById('transactionInput').value = randomId.toUpperCase()
        }
    </script>
@endpush
