@extends('layouts.app')

@section('title', $package->name)

@section('content')
    <h1>{{ $package->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('shop.packages.variables', $package) }}">
                @csrf

                @if(is_numeric($price))
                    <input type="hidden" name="price" value="{{ $price }}">
                @endif

                @if(is_numeric($quantity))
                    <input type="hidden" name="quantity" value="{{ $quantity }}">
                @endif

                @foreach($package->variables as $variable)
                    @if($variable->type === 'checkbox')
                        <div class="form-check mb-3">
                            <input class="form-check-input @error($variable->name) is-invalid @enderror"
                                   type="checkbox"
                                   name="{{ $variable->name }}"
                                   id="{{ $variable->name }}"
                                   @required($variable->is_required)
                                   @checked(old($variable->name))
                            >

                            <label class="form-check-label" for="{{ $variable->name }}">
                                {{ $variable->description }}
                                @if($variable->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @error($variable->name)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @elseif($variable->type === 'dropdown')
                        <div class="mb-3">
                            <label for="{{ $variable->name }}" class="form-label">
                                {{ $variable->description }}
                                @if($variable->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            <select class="form-select @error($variable->name) is-invalid @enderror"
                                    name="{{ $variable->name }}" id="{{ $variable->name }}"
                                    @required($variable->is_required)>
                                @foreach($variable->options as $option)
                                    <option value="{{ $option['value'] }}" @selected(old($variable->name) === $option['value'])>
                                        {{ $option['name'] }}
                                    </option>
                                @endforeach
                            </select>

                            @error($variable->name)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="{{ $variable->name }}" class="form-label">
                                {{ $variable->description }}
                                @if($variable->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            <input type="{{ $variable->type }}"
                                   class="form-control @error($variable->name) is-invalid @enderror"
                                   name="{{ $variable->name }}" id="{{ $variable->name }}"
                                   value="{{ old($variable->name) }}"
                                   @required($variable->is_required)
                            >

                            @error($variable->name)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @endif
                @endforeach

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cart-plus"></i>{{ trans('shop::messages.buy') }}
                </button>
            </form>
        </div>
    </div>
@endsection
