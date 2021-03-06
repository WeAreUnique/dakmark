@extends('layouts.master') @section('content')
<hr>
<div class="container">
    <div class="row">
        <h3 class="page-name">
            <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Giỏ Hàng
        </h3>
        <div class="message">
            @include('notifications.status_message') 
            @include('notifications.errors_message')
            @if (session()->has('success_message'))
                <div class="alert alert-success">
                    {{ session()->get('success_message') }}
                </div>
            @endif
            @if (session()->has('error_message'))
                <div class="alert alert-danger">
                    {{ session()->get('error_message') }}
                </div>
            @endif
        </div>
    </div>
    <br> 
    @if (sizeof(Cart::content()) > 0)
    <div class="row">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Hình Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Giá Tiền</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach (Cart::content() as $item)
                <tr>
                    <td>
                        <a href="{{ $item->options->url }}" target="_blank">
                            <img src="{{ $item->options->image }}" alt="" class="img-rounded img-inside">
                        </a>
                    </td>
                    <td>
                        <a href="{{ $item->options->url }}" target="_blank">
                             {{ $item->name }}
                        </a>
                    </td>
                    <td>
                        <div class="input-group spinner" style="width: 120px">
                            <input type="text" class="form-control quantity" data-id="{{ $item->rowId }}" value="{{$item->qty}}" min="0" max="100000">
                            <div class="input-group-btn-vertical">
                                <button class="btn btn-default ajust-quantity" type="button"><i class="fa fa-caret-up"></i></button>
                                <button class="btn btn-default ajust-quantity" type="button"><i class="fa fa-caret-down"></i></button>
                            </div>
                        </div>
                    </td>
                    <td>{!! price_format($item->subtotal, 'VNĐ') !!}</td>
                    <td>
                        <form action="{{ url('cart', [$item->rowId]) }}" method="POST" class="side-by-side">
                            {!! csrf_field() !!}
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="submit" class="btn btn-danger btn-sm" value="Xóa">
                        </form>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="7">

                    </td>
                    <tr/>
            </tbody>
        </table>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <form action="{{ url('/emptyCart') }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="submit" class="btn btn-danger btn" value="Xóa Hết Giỏ Hàng">
            </form>
        </div>
        <div class="col-md-6 bs-example bs-cart">
            <form class="form-horizontal">
                <div class="form-group">
                    <label for="total" class="col-sm-8 control-label text-right">TỔNG SỐ TIỀN</label>
                    <div class="col-sm-4 text-right">
                        <label for="total" class="control-label">{{ Cart::total() }} VNĐ</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10 text-right">
                        <a type="button" class="btn-lg btn-primary" href="{{ url('/order/create') }}"><span class="glyphicon glyphicon-ok"></span> Đặt Hàng</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @else 
    <div class="row">
        <div class="col-md-12">
            <h3 style="color: #d9534f;">@lang('shoppings.cart_noitems')</h3>
        </div>
    </div>
    @endif
</div>
<br> @endsection @section('scripts')
<!-- <script src="{{url('/')}}/public/assets/js/spinner.js"></script> -->

<script>
    (function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.spinner .btn:first-of-type').on('click', function() {
            var btn = $(this);
            var input = btn.closest('.spinner').find('input');
            if (input.attr('max') == undefined || parseInt(input.val()) < parseInt(input.attr('max'))) {
                input.val(parseInt(input.val(), 10) + 1);
                //update cart
                var id = input.attr('data-id');
                $.ajax({
                    type: "PATCH",
                    url: '{{ url("/cart") }}' + '/' + id,
                    data: {
                        'quantity': input.val(),
                    },
                    success: function(data) {
                        window.location.href = '{{ url('/cart') }}';
                    },
                    error:function(){
                        alert('error');
                    }
                });
                //end update cart
            } else {
                btn.next("disabled", true);
            }
        });

        $('.spinner .btn:last-of-type').on('click', function() {
            var btn = $(this);
            var input = btn.closest('.spinner').find('input');
            if (input.attr('min') == undefined || parseInt(input.val()) > parseInt(input.attr('min'))) {
                input.val(parseInt(input.val(), 10) - 1);
                //update cart
                var id = input.attr('data-id');
                $.ajax({
                    type: "PATCH",
                    url: '{{ url("/cart") }}' + '/' + id,
                    data: {
                        'quantity': input.val(),
                    },
                    success: function(data) {
                        window.location.href = '{{ url('/cart') }}';
                    },
                    error:function(){
                        alert('error');
                    }
                });
                //end update cart
            } else {
                btn.prev("disabled", true);
            }
        });


        $('.quantity').on('input', function() {
            var id = $(this).attr('data-id')
            $.ajax({
                type: "PATCH",
                url: '{{ url("/cart") }}' + '/' + id,
                data: {
                    'quantity': this.value,
                },
                success: function(data) {
                    window.location.href = '{{ url('/cart ') }}';
                },
                error:function(){
                    alert('error');
                }
            });
        });
    })();
</script>
@endsection