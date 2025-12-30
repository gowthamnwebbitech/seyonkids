function handleWishlist(buttonSelector = '.wishlist-btn') {
    $(document).on('click', buttonSelector, function() {
        var button = $(this);
        var productId = button.data('id');
        var productUrl = button.data('url');
        var userLogin = button.data('login');
        var $icon = button.find('i'); 
        
        $.ajax({
            url: productUrl,
            type: 'POST',
            data: {
                product_id: productId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.status === 'login_required') {
                    window.location.href = userLogin;
                } 
                else if(response.status === 'added') {
                     $icon.removeClass('bi-heart').addClass('bi-heart-fill');
                      button.attr('data-bs-original-title', 'Added to Wishlist')
                        .tooltip('dispose') 
                        .tooltip();
                        $('.wishlist_count').text('(' + response.wishlist_count + ')');
                } 
                else if(response.status === 'exists') {
                    $icon.removeClass('far').addClass('fas');
                    button.css('color', '#dc3545');
                }
            },
            error: function(err) {
                // console.log('Error:', err);
            }
        });
    });
}

function removeWishlist(buttonse = '.wishlist-remove') {
    $(document).on('click', buttonse, function() {
        var $button = $(this);
        var prodId = $(this).data('product-id');
        var prodUrl = $(this).data('url');
        var userLogin1 = $(this).data('login');

        $.ajax({
            url: prodUrl,
            type: 'POST',
            data: {
                product_id: prodId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response);
                
                if(response.status === 'login_required') {
                    window.location.href = userLogin1;
                } 
                else if(response.status === 'removed') {
                    $button.closest('.product-card').fadeOut();
                    $('.wishlist_count').text('(' + response.wishlist_count + ')');
                }
                else if(response.status === 'not_found') {
                    alert('Wishlist item not found!');
                }
            },
            error: function(err) {
                console.log('Error:', err);
            }
        });
    });
}

function confirmRemove(button, cartId) {
    if (!confirm('Are you sure you want to remove this item from the cart?')) {
        return;
    }

    $.ajax({
        url: $(button).data('url'),
        type: 'POST',
        data: {
            cart_id: cartId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if(response.status === 'success') {
                location.reload();
                // $(button).closest('.cart-item').fadeOut();
            } else {
                alert('Failed to remove item.');
            }
        },
        error: function(err) {
            alert('Something went wrong!');
        }
    });
}

$(document).ready(function() {
    $('.wishlist-btn').on('click', function(){
        handleWishlist();
    });
    $('.wishlist-remove').on('click', function(){        
        removeWishlist();
    });

    $(document).on('click', '.increase_qty', function() {
        const button = $(this);
        const url = button.data('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const msgDiv = $('#sessionMessage');

                if (response.status === 'success') {
                    button.siblings('.item-qty').text(response.new_qty);
                } else {
                    msgDiv.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }
                setTimeout(() => msgDiv.addClass('d-none'), 3000);
                location.reload();
            },
            error: function(err) {
                $('#sessionMessage').removeClass('d-none alert-success').addClass('alert-danger').text('Server error!');
                setTimeout(() => $('#sessionMessage').addClass('d-none'), 3000);
            }

        });
    });
    $(document).on('click', '.decrease_qty', function() {
        const button = $(this);
        const url = button.data('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const msgDiv = $('#sessionMessage');

                if (response.status === 'success') {
                    button.siblings('.item-qty').text(response.new_qty);
                } else {
                    msgDiv.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                }

                setTimeout(() => msgDiv.addClass('d-none'), 3000);
                location.reload();
            },
            error: function(err) {
                $('#sessionMessage')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text('Server error!');
                setTimeout(() => $('#sessionMessage').addClass('d-none'), 3000);
            }
        });
    });
    $(document).on('click', '.increase', function() {
        const id = $(this).data('id');
        const url = increaseCartRoute+'/' + id;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                cartList();
            },
            error: function(err) {
                cartList();
            }
        });
    });
    $(document).on('click', '.decrease', function() {
        const id = $(this).data('id');
        const url = decreaseCartRoute+'/' + id;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                cartList();
            },
            error: function(err) {
                cartList();
            }
        });
    });
    cartList();
});
// function cartList() {
//     $.ajax({
//         url: cartListUrl,
//         method: "GET",
//         dataType: "json",
//         success: function(cartItems) {
//             let html = '';
//             let subtotal = 0;
//             if(cartItems.length === 0){
//                 html = '<p>Your cart is empty.</p>';
//             } else {
//                 $.each(cartItems, function(index, item) {
//                     let itemTotal = item.product.offer_price * item.quantity;
//                     subtotal += itemTotal;

//                     html += `
//                     <div class="cart-item">
//                                         <button class="cart-remove-btn"
//                             data-url="${cartRemoveUrl}"
//                             onclick="confirmRemove(this, ${item.id})">
//                             &times;
//                         </button>
//                         <div class="item-image toy-image">
//                             <img src="${assetBase}${item.product.product_img}" alt="${item.product.product_name}" width="50">
//                         </div>
//                         <div class="item-details">
//                             <div class="item-title">${item.product.product_name}</div>
//                             <div class="quantity-controls">
//                                 <button data-id="${item.id}" class="qty-btn decrease">-</button>
//                                 <input type="text" class="qty-input" value="${item.quantity}" readonly>
//                                 <button data-id="${item.id}" class="qty-btn increase">+</button>
//                             </div>
//                             <div class="item-price">₹ ${itemTotal}</div>
//                         </div>
//                     </div>`;
//                 });
//             }
           
//             $('#cartModal .cart-body').html(html);
//             $('#subtotalAmount').text('₹ ' + subtotal);
//         },
//         error: function(err){
//             console.log(err);
//         }
//     });
// }
$(document).ready(function () {
    cartList();
});

/* =======================
   LOAD CART
======================= */
function cartList() {
    $.ajax({
        url: cartListUrl,
        method: "GET",
        dataType: "json",
        success: function (items) {

            let html = '';
            let subtotal = 0;

            if (items.length === 0) {
                html = `<p class="text-center">Your cart is empty</p>`;
            } else {

                $.each(items, function (i, item) {
                    let total = item.quantity * item.product.offer_price;
                    subtotal += total;

                    html += `
                    <div class="cart-item p-2">
                        <div class="item-image">
                            <img src="${assetBase}${item.product.product_img}" width="60">
                        </div>

                        <div class="item-details">
                            <div class="item-title">${item.product.product_name}</div>

                            <div class="quantity-controls">
                                <button class="qty-btn decrease" data-id="${item.id}">-</button>
                                <input class="qty-input" value="${item.quantity}" readonly>
                                <button class="qty-btn increase" data-id="${item.id}">+</button>
                            </div>

                            <div class="item-price">₹ ${total}</div>
                        </div>

                        <button class="remove-btn" onclick="removeCart(${item.id})"> <i class="fas fa-trash"></i></button>
                    </div>`;
                });
            }

            $('#cartModal .cart-body').html(html);
            $('#subtotalAmount').text('₹ ' + subtotal);
            $('#cart-count').text(items.length);
        }
    });
}

/* =======================
   INCREASE QTY
======================= */
$(document).on('click', '.increase', function () {
    let id = $(this).data('id');

    $.ajax({
        url: increaseCartRoute + '/' + id,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.status === 'success') {
                cartList(); // refresh cart modal
            }
        }
    });
});


/* =======================
   DECREASE QTY
======================= */
$(document).on('click', '.decrease', function () {
    let id = $(this).data('id');

    $.ajax({
        url: decreaseCartRoute + '/' + id,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.status === 'success') {
                cartList(); // refresh cart modal
            }
        }
    });
});


/* =======================
   REMOVE ITEM
======================= */
function removeCart(id) {

    if (!confirm('Remove this item?')) return;

    $.post(removeCartRoute, {
        cart_id: id,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function (response) {

        if (response.status === 'success') {
            location.reload(); // ✅ reload page
        }
    });
}
