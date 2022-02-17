@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <style>
        .bootstrap-select {
            width: 100%!important;
            display: block!important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js" defer></script>
@endpush

@push('footer-scripts')
    <script>
        document.querySelectorAll('select[multiple]').forEach(function (el) {
            el.classList.remove('form-select', 'form-control');
            el.classList.add('selectpicker');
        });
    </script>
@endpush
