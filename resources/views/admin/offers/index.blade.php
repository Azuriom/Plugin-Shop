@extends('admin.layouts.admin')

@section('title', trans('shop::admin.offers.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.name') }}</th>
                        <th scope="col">{{ trans('shop::messages.fields.price') }}</th>
                        <th scope="col">{{ trans('messages.fields.money') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($offers as $offer)
                        <tr>
                            <th scope="row">{{ $offer->id }}</th>
                            <td>{{ $offer->name }}</td>
                            <td>{{ $offer->price }} {{ currency_display() }}</td>
                            <td>{{ format_money($offer->money) }}</td>
                            <td>
                                <a href="{{ route('shop.admin.offers.edit', $offer) }}" class="mx-1" title="{{ trans('messages.actions.edit') }}" data-toggle="tooltip"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('shop.admin.offers.destroy', $offer) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <a class="btn btn-primary" href="{{ route('shop.admin.offers.create') }}">
                <i class="fas fa-plus"></i> {{ trans('messages.actions.add') }}
            </a>
        </div>
    </div>
@endsection
