<nav class="black" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ trans('messages.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">{{ trans('shop::messages.title') }}</a></li>

        @foreach(optional($current ?? null)->getNavigationStack() ?? [] as $breadcrumbLink => $breadcrumbName)
            <li class="breadcrumb-item"><a href="{{ $breadcrumbLink }}">{{ $breadcrumbName }}</a></li>
        @endforeach
    </ol>
    <h2 style="font-family: poppins,sans-serif;"><B>{{ site_name() }}</B></h2>
</nav>

@push('styles')
    <style>
        .forum-big-icon i {
            font-size: 3em;
        }

        .black {
        	border-radius: 4px;
        	background: #444445;
        	-webkit-box-shadow: 0 5px 8px rgba(0,0,0,.075);
        	box-shadow: 0 5px 8px rgba(0,0,0,.075);
        	margin-bottom: 20px;
        }

        .breadcrumb {
        	background: #444445;
        }

        @media (max-width: 575px) {
            .forum-big-icon {
                padding-left: 5px;
                padding-right: 0;
            }

            .forum-big-icon i {
                font-size: 2em;
            }
        }
    </style>
@endpush
