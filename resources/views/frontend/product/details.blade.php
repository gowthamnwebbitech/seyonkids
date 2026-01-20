@extends('frontend.layouts.app')
@section('content')
    <style>
        .wishlist-section .btn {
            transition: all 0.25s ease-in-out;
        }
        .wishlist-section .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

    <section class="container my-5">
        <div class="product-container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-md-6">
                    <div class="image-section text-center position-relative">
                        <!-- Main Image with Zoom -->
                        <img src="{{ asset($productDetails->product_img ?? '') }}" alt="Main Product Image"
                            class="main-image img-fluid mb-3" id="mainImage">

                        <!-- Zoomed view -->
                        <div id="zoomResult" class="zoom-result"></div>

                        <!-- Thumbnails + Arrows -->
                        <div class="thumbnail-container d-flex justify-content-center align-items-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm" id="prevBtn">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            @foreach ($productImages as $key => $image)
                                <img src="{{ asset($image->path) }}"
                                    class="thumbnail img-thumbnail {{ $key === 0 ? 'active' : '' }}"
                                    style="width:80px; height:80px; cursor:pointer;">
                            @endforeach

                            <button class="btn btn-outline-secondary btn-sm" id="nextBtn">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-md-6">
                    <div class="product-details">
                        <!-- Rating -->
                        @php
                            $averageRating = round($productDetails->reviews()->avg('star_count'), 1);
                            $ratingCount   = $productDetails->reviews()->count();
                        @endphp

                        <div class="rating-section mb-3">
                            <span class="star-rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($averageRating))
                                        <i class="fas fa-star text-warning"></i>
                                    @elseif ($i - $averageRating < 1)
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                    @else
                                        <i class="far fa-star text-warning"></i>
                                    @endif
                                @endfor
                            </span>
                            <span class="rating-text">
                                {{ $averageRating ?? '0' }} Star Rating ({{ $ratingCount }} User Feedback{{ $ratingCount > 1 ? 's' : '' }})
                            </span>
                        </div>

                        <!-- Product Title -->
                        <h1 class="product-title">{{ $productDetails->product_name }}</h1>

                        <!-- Product Meta Info -->
                        <div class="product-meta">
                            <div class="col-5">
                                <div class="meta-item">
                                    @if($productDetails->sku)
                                        <span class="meta-label">SKU:</span>
                                        <span class="meta-value">{{ $productDetails->sku }}</span>
                                    @endif
                                </div>
                                <div class="meta-item">
                                    @if($productDetails->no_of_pages)
                                        <span class="meta-label">No of Pages:</span>
                                        <span class="meta-value">{{ $productDetails->no_of_pages }} pages</span>
                                    @endif
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Category:</span>
                                    <span class="meta-value"><a href="#"
                                            class="brand-link">{{ $productDetails->category->name ?? '' }}</a></span>
                                </div>
                                {{-- <div class="meta-item">
                                    <span class="meta-label">Brand:</span>
                                    <span class="meta-value"><a href="#" class="brand-link">Apple</a></span>
                                </div> --}}
                            </div>
                            <div class="col-5">
                                <div class="meta-item">
                                    <span class="meta-label">Availability:</span>
                                    @if ($productDetails->quantity >= 4)
                                        <span class="meta-value text-success">In Stock</span>
                                    @elseif ($productDetails->quantity >= 1)
                                        <span class="meta-value text-warning">Only {{ $productDetails->quantity }} left</span>
                                    @else
                                        <span class="meta-value text-danger">Out of Stock</span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- Price Section -->
                        <div class="price-section">
                            <span class="current-price">₹{{ $productDetails->offer_price }}</span>
                            <span class="original-price">₹{{ $productDetails->orginal_rate }}</span>
                            @if ($productDetails->discount)
                                <span class="offer-badge">{{ $productDetails->discount }}% OFF</span>
                            @endif
                        </div>

                        <!-- Countdown Timer -->
                        {{-- <div class="countdown-section col-col">
                            <div class="countdown-text">Hurry up! Sale ends in:</div>
                            <div class="countdown-timer">
                                <div class="countdown-item">
                                    <div class="countdown-number" id="hours">00</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-number" id="minutes">05</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-number" id="seconds">59</div>
                                </div>
                                <div class="countdown-item">
                                    <div class="countdown-number" id="milliseconds">47</div>
                                </div>
                            </div>
                        </div> --}}

                        <form action="{{ route('buy.now') }}" method="POST" id="buyNowForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $productDetails->id }}">

                            <div class="d-flex justify-content-between col-col">
                                <!-- Quantity Section -->
                                <div class="quantity-section">
                                    <div class="quantity-control">
                                        <button type="button" class="quantity-btn" id="decreaseBtn">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="text" class="quantity-input" id="quantityInput" 
                                            name="quantity"
                                            value="1"
                                            maxlength="2"
                                            max="{{ $productDetails->quantity }}" 
                                            min="1"
                                            inputmode="numeric">
                                        <button type="button" class="quantity-btn" id="increaseBtn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    @if ($productDetails->quantity > 0)
                                        @auth
                                            @if ($productDetails->cart && $productDetails->cart->product_id == $productDetails->id)
                                                <!-- Already in cart -->
                                                <a href="{{ route('show.cart.table') }}" class="btn-add-cart" style="text-decoration: none">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    GO TO CART
                                                </a>
                                            @else
                                                <!-- Not in cart -->
                                                <a href="{{ route('addto.cart', $productDetails->id) }}" 
                                                class="btn-add-cart" 
                                                id="addToCartBtn" 
                                                style="text-decoration: none">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    ADD TO CART
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('user.login') }}" class="btn-add-cart" style="text-decoration: none">
                                                <i class="fas fa-user"></i> LOGIN TO BUY
                                            </a>
                                        @endauth

                                        <form action="{{ route('buy.now') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $productDetails->id }}">
                                            <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                                            <button type="submit" class="btn-buy-now" id="buyNowBtn">BUY NOW</button>
                                        </form>
                                    @else
                                        <button type="button" class="btn-add-cart" disabled>OUT OF STOCK</button>
                                    @endif
                                </div>

                            </div>
                        </form>


                        <!-- Wishlist and Share -->
                        <div class="wishlist-share">
                            <div class="wishlist-section">
                                @if ($productDetails->isWishlist)
                                    <a href="{{ route('show.wishlist.list') }}"
                                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 wishlist-btn"
                                    title="Remove from Wishlist"
                                    style="border-radius: 50px; font-weight: 500; white-space: nowrap;">
                                        <i class="bi bi-heart-fill text-danger"></i>
                                        <span class="ms-4 text-dark">Remove from Wishlist</span>
                                    </a>
                                @else
                                    @auth
                                        <a href="javascript:void(0)"
                                        class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 wishlist-btn"
                                        data-id="{{ $productDetails->id }}"
                                        data-url="{{ route('addto.wishlist', $productDetails->id) }}"
                                        data-login="{{ route('user.login') }}"
                                        title="Add to Wishlist"
                                        style="border-radius: 50px; font-weight: 500; white-space: nowrap;">
                                            <i class="bi bi-heart"></i>
                                            <span class="ms-4">Add to Wishlist</span> 
                                        </a>
                                    @else
                                        <a href="{{ route('user.login') }}?type=guest"
                                        class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-2 px-3 py-2"
                                        title="Add to Wishlist"
                                        style="border-radius: 50px; font-weight: 500; white-space: nowrap;">
                                            <i class="bi bi-heart"></i>
                                           <span class="ms-4">Add to Wishlist</span> 
                                        </a>
                                    @endauth
                                @endif
                            </div>

                            {{-- Share Buttons --}}
                            <div class="d-flex align-items-center mt-3">
                                <span class="me-3">Share product:</span>
                                <div class="share-buttons d-flex gap-2">

                                    {{-- Copy Link --}}
                                    <button onclick="copyUrl('{{ route('product.details.show', $productDetails->id) }}')" class="share-btn">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>

                                    {{-- WhatsApp --}}
                                    <button class="share-btn whatsapp"
                                        onclick="shareOnWhatsApp('{{ route('product.details.show', $productDetails->id) }}', '{{ $productDetails->product_name }}')">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>

                                    {{-- Twitter / X --}}
                                    <button class="twitter"
                                        onclick="shareOnTwitter('{{ route('product.details.show', $productDetails->id) }}', '{{ $productDetails->product_name }}')">
                                        <i class="fa-brands fa-x-twitter"></i>
                                    </button>

                                    {{-- Facebook --}}
                                    <button class="facebook"
                                        onclick="shareOnFacebook('{{ route('product.details.show', $productDetails->id) }}')">
                                        <i class="fab fa-facebook-f"></i>
                                    </button>

                                </div>
                            </div>
                        </div>

                        <script>
                            function copyUrl(url) {
                                navigator.clipboard.writeText(url);
                                document.title = '✅ Product link copied!';
                                setTimeout(() => {
                                    document.title = '{{$productDetails->product_name}}';
                                }, 2000);
                            }

                            function shareOnWhatsApp(url, title) {
                                const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' - ' + url)}`;
                                window.open(whatsappUrl, '_blank');
                            }

                            function shareOnTwitter(url, title) {
                                const tweetUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                                window.open(tweetUrl, '_blank', 'width=600,height=400');
                            }

                            function shareOnFacebook(url) {
                                const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                                window.open(fbUrl, '_blank', 'width=600,height=400');
                            }
                        </script>

                        <!-- Payment Security -->
                        <div class="payment-security">
                            <div class="security-title">
                                <i class="fas fa-shield-alt me-2"></i>
                                100% Guarantee Safe Checkout
                            </div>
                            <div class="payment-methods">
                                <div class="payment-card visa"><img src="{{ asset('frontend/img/maestro.png') }}"
                                        alt=""></div>
                                <div class="payment-card mastercard"><img
                                        src="{{ asset('frontend/img/visa-electron.png') }}" alt=""></div>
                                <div class="payment-card amex"><img src="{{ asset('frontend/img/visa.png') }}"
                                        alt=""></div>
                                <div class="payment-card paypal"><img src="{{ asset('frontend/img/paypal.png') }}"
                                        alt=""></div>
                                <div class="payment-card discover"><img src="{{ asset('frontend/img/american.png') }}"
                                        alt=""></div>
                                <div class="payment-card"><img src="{{ asset('frontend/img/master.png') }}"
                                        alt=""></div>
                                <div class="payment-card"><img src="{{ asset('frontend/img/delta.png') }}"
                                        alt=""></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- description --}}
    <section>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab"
                                data-bs-target="#description" type="button" role="tab" aria-controls="description"
                                aria-selected="true">
                                Description
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                                type="button" role="tab" aria-controls="reviews" aria-selected="false">
                                Reviews
                            </button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel"
                            aria-labelledby="description-tab">
                            <p>{!! $productDetails->description ?? '' !!}</p>
                        </div>

                        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                            <p>Customer reviews will be displayed here. This section would typically contain user ratings,
                                comments, and feedback about the product.</p>

                            <p>You can add review components, star ratings, user avatars, and review text in this section.
                                The content structure would be similar to the description tab but focused on customer
                                feedback.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Best Seller Products --}}
    @if ($related_products->isNotEmpty())
        <section class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h4 mb-0 fs-1">Related Products</h2>
                <a href="#" class="d-inline-flex align-items-center text-decoration-none fw-medium text-dark">
                    View all Deals
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- Responsive grid: 2 cols on xs, 3 on md, 4 on lg, 5 on xl+ -->
            <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                <!-- CARD -->
                @foreach ($related_products as $seller)
                    <div class="col">
                        <div class="card product-card1">
                            <!-- <span class="badge text-dark discount-badge">25% OFF</span> -->
                            <div class="ratio ratio-4x3 position-relative">
                                <img src="{{ asset($seller->product_img) }}" class="card-img-top object-fit-cover"
                                    alt="Small Parking Lot Wooden Magnetic Alphabet Maze Puzzle for Kid">
                                <div class="product-actions">
                                    <a href="#" class="card-btun btn-sm action" title="Wishlist"
                                        aria-label="Add to wishlist">
                                        <i class="bi bi-heart"></i>
                                    </a>
                                    <a href="{{ route('product.details.show', $seller->id) }}"
                                        class="card-btun btn-sm action" title="Quick view" aria-label="Quick view">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="#" class="card-btun btn-sm action" title="Add to cart"
                                        aria-label="Add to cart">
                                        <i class="bi bi-cart"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <p class="card-title small text-truncate-2 mb-1">{{ $seller->product_name }}</p>
                                <div class="price small">
                                    <span class="text-danger fw-semibold">{{ $seller->offer_price }}</span>
                                    <span
                                        class="text-muted text-decoration-line-through">{{ $seller->orginal_rate }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <!-- Add as many .col as needed... -->
            </div>
        </section>
    @endif

    {{-- Best Seller Products --}}
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $("#decreaseBtn").on("click", function() {
            let $input = $("#quantityInput");
            let currentValue = parseInt($input.val()) || 1;
            let min = parseInt($input.attr("min")) || 1;

            if (currentValue > min) {
                $input.val(currentValue - 1);
            }

            // update hidden input for Buy Now
            $("#buyNowQuantity").val($input.val());
        });

        $("#increaseBtn").on("click", function() {
            let $input = $("#quantityInput");
            let currentValue = parseInt($input.val()) || 1;
            let max = parseInt($input.attr("max")) || 99;

            if (currentValue < max) {
                $input.val(currentValue + 1);
            }

            // update hidden input for Buy Now
            $("#buyNowQuantity").val($input.val());
        });

        // optional: also sync if user types manually
        $("#quantityInput").on("input", function() {
            let val = parseInt($(this).val()) || 1;
            let max = parseInt($(this).attr("max")) || 99;
            let min = parseInt($(this).attr("min")) || 1;

            if (val > max) val = max;
            if (val < min) val = min;

            $(this).val(val);
            $("#buyNowQuantity").val(val);
        });


        let $thumbnails = $(".thumbnail");
        let $mainImage = $("#mainImage");
        let currentIndex = 0;

        // Thumbnail click
        $thumbnails.on("click", function() {
            $thumbnails.removeClass("active border-primary");
            $(this).addClass("active border-primary");

            let newSrc = $(this).attr("src");
            $mainImage.attr("src", newSrc);

            currentIndex = $thumbnails.index(this);
        });

        // Prev button
        $("#prevBtn").on("click", function() {
            if ($thumbnails.length === 0) return;
            currentIndex = (currentIndex - 1 + $thumbnails.length) % $thumbnails.length;
            $thumbnails.eq(currentIndex).trigger("click");
        });

        // Next button
        $("#nextBtn").on("click", function() {
            if ($thumbnails.length === 0) return;
            currentIndex = (currentIndex + 1) % $thumbnails.length;
            $thumbnails.eq(currentIndex).trigger("click");
        });

        // Initialize main image with the first thumbnail (if exists)
        if ($thumbnails.length > 0) {
            $thumbnails.first().trigger("click");
        }
    });
    $(document).ready(function() {
        let $thumbnails = $(".thumbnail");
        let $mainImage = $("#mainImage");
        let currentIndex = 0;

        // Thumbnail click
        $thumbnails.on("click", function() {
            $thumbnails.removeClass("active border-primary");
            $(this).addClass("active border-primary");

            let newSrc = $(this).attr("src");
            $mainImage.attr("src", newSrc);

            currentIndex = $thumbnails.index(this);
        });

        // Prev button
        $("#nextBtn").on("click", function() {
            if ($thumbnails.length === 0) return;
            currentIndex = (currentIndex - 1 + $thumbnails.length) % $thumbnails.length;
            $thumbnails.eq(currentIndex).trigger("click");
        });

        // Next button
        $("#prevBtn").on("click", function() {
            if ($thumbnails.length === 0) return;
            currentIndex = (currentIndex + 1) % $thumbnails.length;
            $thumbnails.eq(currentIndex).trigger("click");
        });

        // Initialize first thumbnail as active
        if ($thumbnails.length > 0) {
            $thumbnails.first().trigger("click");
        }

    });
</script>
<script src="{{ asset('frontend/js/product-detail.js') }}"></script>