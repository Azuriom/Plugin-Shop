@extends('layouts.app')

@section('title', trans('shop::messages.payment.title'))

@push('footer-scripts')
    <script src="https://brainblocks.io/brainblocks.min.js"></script>
    <script type="text/javascript">
        // Render the Nano button
        brainblocks.Button.render({
      
            // Pass in payment options
            payment: {
                currency: '{{ $currency }}',
                amount: '{{ $amount }}',
                destination: '{{ $public_key }}'
            },
      
            // Handle successful payments
            onPayment: function(data) {
                // 4. Call BrainBlocks API to verify data.token
                var form = new FormData()
                form.append('token', data.token)
                form.append('id','{{ $payment_id }}')
                $.ajax({
                    url : '{{ route('shop.payments.notification', 'brainblocks') }}',
                    type : 'POST',
                    data : form,
                    processData: false,
                    contentType: false,
                    dataType : 'json',     
                    success : function(res){ 
                        if(res.status === 'success')
                            location.href = "{{ route('shop.payments.success', 'brainblocks') }}"
                        else document.querySelector('#feedback').innerHTML = '<div class="alert alert-danger" role="alert">'+res.message+'</div>'       
                    },
                    error: function(res){
                        document.querySelector('#feedback').innerHTML = '<div class="alert alert-danger" role="alert">'+res.message+'</div>'
                    }
                });
            }
        }, '#nano-button');
      </script>
@endpush

@section('content')
    <div class="container content">
        <h1>{{ trans('shop::messages.payment.title') }}</h1>
        <div id="feedback"></div>
        <div style="text-align: -webkit-center;" id="nano-button"></div>
    </div>
@endsection