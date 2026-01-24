@extends('frontend.layouts.app')
@section('content')
<style>
    .gift-wrap-label {
    position: relative;
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    text-align: center;
}

.gift-wrap-label img.gift-wrap-option {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.gift-wrap-label:hover img.gift-wrap-option {
    transform: scale(1.05);
}

.gift-wrap-label input:checked + img {
    border: 2px solid #007bff;
    box-shadow: 0 0 5px #007bff;
}

.gift-wrap-name {
    display: block;
    font-size: 0.85rem;
    color: #333;
    margin-top: 5px;
}
.button-success {
       background: green;
    color: #ffffff;
    border: none;
    padding: 14px;
    min-width: 100px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
    display: flex;
    align-items: center;
    /* gap: 8px; */
    justify-content: center;
    height: 42px;
    margin-right: 10px
}
.btn-div{
    display: flex;
    align-content: center;
    justify-content: start;
    flex-wrap: wrap
}
.common-btn{
    height: 42px;
    width: max-content !important;
    padding: 14px 25px
}
</style>
    <!-- Breadcrumb Section -->
    <section class="breadcrumb-section">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item "><a class="text-decoration-none text-dark" href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cart</li>
            </ol>
        </nav>
        <h4 class="page-title">Cart</h4>
    </section>

    <section>
        <div id="sessionMessage" class="alert d-none" role="alert"></div>
        <div class="container cart-cont">
            <div class="row">
                @if($cart_lists->isNotEmpty())
                    <div class="col-lg-8">
                        <!-- Cart Header -->
                        <div class="cart-header">
                            <div class="row w-100">
                                <div class="col-1"></div>
                                <div class="col-4">Product</div>
                                <div class="col-2">Price</div>
                                <div class="col-3">Quantity</div>
                                <div class="col-2 text-end">Subtotal</div>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        @php
                            $subtotal = 0;
                        @endphp
                        @if($cart_lists->isNotEmpty())
                            @foreach ($cart_lists as $cart)
                                @php
                                    $subtotal += $cart->product->offer_price * $cart->quantity;
                                @endphp
                                <div class="cart-item">
                                    <div class="row align-items-center w-100">
                                        <div class="col-1">
                                            <button class="remove-btn" data-url="{{ route('cart.remove') }}" onclick="removeCart( {{$cart->id}})">×</button>
                                        </div>
                                        <style>
                                            .color_picker label {
                                                border: 1px solid #b5b5b5;
                                                border-radius: 10px;
                                                display: inline-block;
                                                width: 25px;
                                                height: 25px;
                                                margin-right: 4px;
                                            }
                                        </style>
                                        <div class="col-4">
                                            <div class="product-info">
                                                <img src="{{ asset($cart->product->product_img) }}"
                                                    alt="Product" class="product-image">
                                                <h6 class="product-name">{{ $cart->product->product_name }}</h6>
                                                
                                                <div class="color_picker ms-2">
                                                    @if($cart->color_id && $cart->colorData)
                                                        @if($cart->colorData)
                                                            <label for="color" title="{{ $cart->colorData->color->color }}" style="background-color: {{ $cart->colorData->color->color_code }}">
                                                                <span style="background-color: {{ $cart->colorData->color->color_code }}"></span>
                                                            </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <span class="price-text">₹ {{ $cart->product->offer_price ?? ''}}</span>
                                        </div>
                                        <div class="col-3">
                                            <div class="quantity-control">
                                                <button data-url="{{ route('decrease.qty',$cart->id) }}" class="quantity-btn decrease_qty">−</button>
                                                <input type="text" class="quantity-input" min="1" value="{{ $cart->quantity }}" max="{{ $cart->product->quantity }}" readonly>
                                                <button data-url="{{ route('increase.qty',$cart->id) }}" class="quantity-btn increase_qty">+</button>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <span class="price-text subtotal">₹ {{ $cart->product->offer_price * $cart->quantity }} </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Coupon Section -->
                        {{-- <div class="coupon-section">
                            <div class="coupon-controls">
                                <input type="text" class="coupon-input" placeholder="Enter Coupon Code">
                                <button class="apply-btn">APPLY</button>
                            </div>
                            <div>
                                <a href="#" class="update-cart-link">Update Cart</a>
                            </div>
                        </div> --}}
                    </div>

                    <!-- Cart Totals -->
                    <div class="col-lg-4">
                        <div class="cart-totals-card p-4">
                            <h5 class="fw-bold mb-3">Cart Totals</h5>

                            <!-- Subtotal -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span class="price-red">₹ {{ $subtotal }}</span>
                            </div>
                            <hr>
                            <!-- Gift Options -->
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                    <label class="fw-semibold">Gift Wrap</label>
                                   <div>
                                        <label class="switch">
                                            <input type="checkbox" id="test" @checked($cart_lists->first()->gift_wrap_id)>
                                            <span class="slider round"></span>
                                        </label>
                                        <span>Yes</span>
                                   </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                    <label class="fw-semibold">Gift Message</label>
                                    <div>
                                        <label class="switch">
                                            <input type="checkbox" id="test2" @checked($cart_lists->first()->gift_message)>
                                            <span class="slider round"></span>
                                        </label>
                                        <span>Yes</span>
                                    </div>
                                </div>
                            </div>
                            @if(isset($cart_lists->first()->gift_wrap_id))
                                @php
                                    $subtotal = $subtotal + $cart_lists->first()->giftWrap->price;
                                @endphp
                            @endif
                            <!-- Gift Wrap Options -->
                            <div class="gift-options mb-3">
                                <div class="d-flex gap-3 flex-wrap">
                                    @foreach ($gift_wraps as $wrap)
                                        <label class="gift-wrap-label {{ session('selected_gift_wrap') == $wrap->id ? 'active' : '' }}">
                                            <input 
                                                type="radio" 
                                                name="gift_wrap_id" 
                                                value="{{ $wrap->id }}" 
                                                class="gift-wrap-radio d-none"
                                                @checked($cart_lists->first()->gift_wrap_id == $wrap->id)
                                            >
                                            <img 
                                                src="{{ asset($wrap->image) }}" 
                                                alt="{{ $wrap->name }}" 
                                                class="gift-wrap-option"
                                            >
                                            <span class="gift-wrap-name">+ ₹{{ $wrap->price }}</span>
                                        </label>
                                    @endforeach

                                </div>
                            </div>
                            <!-- Gift Message -->
                            <div class="mb-3">
                                <div class="gift-message">
                                    <div id="giftAlert" class="alert alert-success mt-2" style="display: none;" role="alert">
                                        Gift message saved successfully!
                                    </div>
                                    <p class="fw-semibold text-dark mb-2">Gift Message</p>
                                    <textarea id="gift_message" class="form-control" rows="3" placeholder="Write your message...">{!! $cart_lists->first()->gift_message !!}</textarea>
                                    <div id="messageAlert" class="alert alert-danger mt-2" style="display: none;" role="alert">
                                        Message field is required!
                                    </div>
                                </div>
                                <div class="btn-div">
                                     <div class="gift-message-save">
                                        <button id="saveGiftMessage" class="button-success btn mt-2">Save</button> 
                                     </div>

                                    @if($cart_lists->first()->gift_wrap_id)
                                        <button id="removeGiftMessage" class="common-btn btn-danger w-max mt-2">Remove</button>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <!-- Total -->
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Total</span>
                                <span class="final-price price-red fw-bold">Rs.{{ $subtotal }}</span>
                            </div>

                            <!-- Checkout Button -->
                            <a href="{{ route('product.proceed_to_checkout') }}" class="btn btn-danger w-100 rounded-3 py-2 mb-2 fw-semibold">
                                Proceed to checkout
                            </a>
                            <a href="{{ route('home') }}" class="d-block text-center text-dark">Continue Shopping</a>
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                        <span class="text-muted fs-5">😔Your Cart is Empty</span>
                    </div>
                @endif
            </div>
        </div>
    </section>
  <script>
    $(document).ready(function() {
        $('.gift-wrap-radio').on('click', function() {
            var giftWrapId = $(this).val();

            $.ajax({
                url: "{{ route('wrap.update') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    gift_wrap_id: giftWrapId
                },
                success: function(response) {
                    location.reload();
                    if (response.status === 'success') {
                        $('.final-price').text('Rs.' + response.new_total);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
        $('#saveGiftMessage').on('click', function(e) {
            e.preventDefault();
            var message = $('#gift_message').val();

            if (message.trim() === '') {
                $('#messageAlert').fadeIn();
                setTimeout(function() {
                    $('#messageAlert').fadeOut(); 
                }, 3000);
                return;
            }

            $.ajax({
                url: "{{ route('wrap.message.update') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    gift_message: message
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#giftAlert').fadeIn();
                        setTimeout(function() {
                            $('#giftAlert').fadeOut(); 
                        }, 3000);
                        location.reload();
                    } else {
                        alert('Something went wrong.');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
        $('#removeGiftMessage').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('wrap.message.remove') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Something went wrong.');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
    });
    </script>
  <script>

    const cb1 = document.getElementById('test');
    const cb2 = document.getElementById('test2');
    const options = document.querySelector('.gift-options');
    const message = document.querySelector('.gift-message');
    const save = document.querySelector('.gift-message-save');
    const remove = document.querySelector('.gift-message-remove');

    function updateOptions() {
        if (!options || !cb1) return;
        options.classList.toggle('d-none', !cb1.checked);
    }

    function updateMessage() {
        if (!message || !cb2) return;
        message.classList.toggle('d-none', !cb2.checked);
        save.classList.toggle('d-none', !cb2.checked);
    }

    cb1?.addEventListener('change', updateOptions);
    cb2?.addEventListener('change', updateMessage);

    updateOptions();
    updateMessage();
</script>
@endsection