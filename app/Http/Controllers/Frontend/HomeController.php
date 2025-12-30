<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Admin\MileStoneController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Upload;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Product;
use App\Services\SmsService;
use App\Mail\ContactFormMail;
use App\Models\Address;
use App\Models\CallToAction;
use App\Models\Cart;
use App\Models\GiftWrap;
use App\Models\MilestoneSetting;
use App\Models\ShippingPrice;
use App\Models\ShopByAge;
use App\Models\ShopByPrice;
use App\Models\ShopByReels;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function index(){
        $categories = ProductCategory::where('status', 1)->get();
        $shop_by_age = ShopByAge::where('status', 1)->get();
        $shop_by_prices = ShopByPrice::where('status', 1)->get();
        $shop_by_reels = ShopByReels::where('status', 1)->get();
        $call_to_actions = CallToAction::where('status', 1)->get()->keyBy('name');
        $products = Product::where('status', 1)->get();
        $new_arrivals = $products->where('new_arrival', 1);
        $best_seller = $products->where('best_sellers', 1);
        return view('frontend.index',compact('new_arrivals','shop_by_reels','best_seller','categories','shop_by_age','call_to_actions','shop_by_prices'));
    }
    public function searchList(Request $request)
    {
        // print_r($request->all()); exit();
        $user        = Auth::user();
        $category    = ProductCategory::where('status',1)->latest()->get();
        $subcategory = ProductSubCategory::where('status',1)->latest()->get();
        
        $search_text = $request->search_text;

        $search = null;
        $product = Product::query();
        
        
        if($search_text)
        {
            $product     = Product::where('product_name', 'LIKE', "%{$search_text}%")
                           ->where('status', 1)
                           ->latest()->get();
            $product_count = Product::where('status', 1)->count();
        }
        else
        {
            $product     = Product::where('status',1)->latest()->get();
            
            $product_count = Product::where('status', 1)->count();
            
        }
        
        return view('frontend.product.search_product',compact('user','category','subcategory','product','product_count'));
    }
    
    public function search(Request $request)
    {
        $user = Auth::user();

        // 🏷️ Load filter data
        $categories = ProductCategory::where('status', 1)->latest()->get();
        $subcategories = ProductSubCategory::where('status', 1)->latest()->get();

        // Constant range values (absolute min/max)
        $min_price = Product::where('status', 1)->min('offer_price');
        $max_price = Product::where('status', 1)->max('offer_price');

        // Current user-selected range (req_min, req_max)
        $req_min_price = $request->req_min ?? $min_price;
        $req_max_price = $request->req_max ?? $max_price;

        $selected_categories = $request->selected_categories ?? [];
        $selected_subcategories = $request->selected_subcategories ?? [];
        $selected_submenus = $request->selected_submenus ?? [];
        $sort_by = $request->sort_by ?? null;
        $search = trim($request->search);

        // 🧩 Base query
        $productQuery = Product::query()->where('status', 1);

        // 🔍 Search by name or keyword
        if (!empty($search)) {
            $productQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                ->orWhere('keyword', 'LIKE', "%{$search}%");
            });
        }

        // 🏷️ Category filter
        if (!empty($selected_categories)) {
            $productQuery->whereIn('category_id', $selected_categories);
        }
        if (!empty($selected_subcategories)) {
            $productQuery->whereIn('subcategory', $selected_subcategories);
        }
        if (!empty($request->shop_by_age_id)) {
            $productQuery->whereHas('shopByAges', function ($q) use ($request) {
                $q->whereIn('shop_by_age_id', (array) $request->shop_by_age_id);
            });
        }

        // 🏷️ Submenu filter
        if (!empty($selected_submenus)) {
            $productQuery->whereIn('sub_menu_id', $selected_submenus);
        }

        // 💰 Price filter (based on req_min, req_max)
        $productQuery->whereBetween('offer_price', [$req_min_price, $req_max_price]);

        if($sort_by == "low-to-high"){
            $productQuery->orderBy('offer_price','asc');
        }
        elseif($sort_by == "high-to-low"){
            $productQuery->orderBy('offer_price','desc');
        }
        elseif($sort_by == "new-arrival"){
            $productQuery->where('new_arrival',1);
        }
        elseif($sort_by == "best-selling"){
            $productQuery->where('best_sellers',1);
        }
        // 📦 Fetch filtered products
        $products = $productQuery->latest()->paginate(12)->withQueryString();


        // 🧮 Product count
        $product_count = $products->total();

        return view('frontend.product.category', compact(
            'user',
            'categories',
            'subcategories',
            'products',
            'selected_categories',
            'product_count',
            'min_price',        // constant min
            'max_price',        // constant max
            'req_min_price',    // current selected min
            'req_max_price',    // current selected max
            'search',
            'sort_by'
        ));
    }

    public function subCategoryList($id,Request $request)
    {
        $user        = Auth::user();
        $category    = ProductCategory::latest()->get();
        $subcategory = ProductSubCategory::latest()->get();
        
        $minOfferPrice = Product::where('category_id', $id)
                        ->where('status', 1)
                        ->min('offer_price');
                    
        $maxOfferPrice = Product::where('category_id', $id)
            ->where('status', 1)
            ->max('offer_price');
            
            

        $selected_categories = array();
        $search = null;
        $product = Product::query();
        
        
        if ($request->has('selected_categories')) {
            $selected_categories = $request->selected_categories;
            $blog_categories = ProductCategory::whereIn('id', $selected_categories)->pluck('id')->toArray();

            $product->whereIn('category_id', $blog_categories);
            $product = $product->where('status', 1)->orderBy('created_at', 'desc')->get();
        }
        else
        {
            $product     = Product::where('subcategory',$id)->where('status',1)->latest()->get();
        }
        
        
        
        return view('frontend.product.subcategory',compact('user','subcategory','category','product','selected_categories','minOfferPrice','maxOfferPrice'));
    }

    public function prodCategoryList(){
        $categories = ProductSubCategory::where('status',1)->get();
        return view('frontend.product.category-list',compact('categories'));
    }
    
    
    public function productDetails($id)
    {   
        $productImages  = Upload::where('product_id',$id)->get();
        $productDetails = Product::where('id', $id)->first();
        $productImages  = Upload::where('product_id', $id)->get();
        $related_products = Product::where('category_id', $productDetails->category_id)->get();   
        return view('frontend.product.details',compact('productDetails','productImages','related_products'));
    }
    
    
    public function getProductDetails(Request $request)
    {
        $productId = $request->input('id');
        $product = Product::find($productId);

        return view('frontend.product.product_details', ['product' => $product])->render();
    }
    
    public function showCartTable()
    {
        $products = Product::all();
        $gift_wraps = GiftWrap::where('status',1)->get();
        $cart_lists = Cart::where('user_id', auth()->user()->id)->get();
        return view('frontend.product.cart', compact('gift_wraps','products','cart_lists'));
    }
    
    public function wishlistList()
    {
        $product = Product::all();
        $wishlists = Wishlist::where('user_id', auth()->user()->id)->get();
        return view('frontend.product.wishlist', compact('product','wishlists'));
    }
    
    public function addToWishlist(Request $request)
    {
        if(!auth()->check()) {
            return response()->json(['status' => 'login_required']);
        }

        $userId = auth()->id();
        $productId = $request->product_id;

        $exists = Wishlist::where('user_id', $userId)
                        ->where('product_id', $productId)
                        ->exists();

        if($exists) {
            return response()->json(['status' => 'exists']);
        }
        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        $wishlist_count = Wishlist::where('user_id', $userId)->count();
        return response()->json(['status' => 'added', 'wishlist_count' => $wishlist_count]);
    }
    
    public function removeWishlist(Request $request){
        $userId = auth()->id();
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();
        if ($wishlist) {
            $wishlist->delete();
            $wishlist_count = Wishlist::where('user_id', $userId)->count();
            return response()->json(['status' => 'removed','wishlist_count' => $wishlist_count]);
        }

        return response()->json(['status' => 'not_found']);
    }
        
    public function addToCartBuy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            abort(404);
        }
    
        $cart = session()->get('cart');
    
        if (!$cart) {
            $cart = [
                $id => [
                    "id" => $product->id,
                    "gst" => $product->gst,
                    "product_name" => $product->product_name,
                    "quantity"     => 1,
                    "offer_price"  => $product->offer_price,
                    "product_img"  => $product->product_img
                ]
            ];
            session()->put('cart', $cart);
        } else {
            if (isset($cart[$id])) {
                $cart[$id]['quantity']++;
            } else {
                $cart[$id] = [
                    "id" => $product->id,
                    "gst" => $product->gst,
                    "product_name" => $product->product_name,
                    "quantity" => 1,
                    "offer_price" => $product->offer_price,
                    "product_img" => $product->product_img
                ];
            }
            session()->put('cart', $cart);
        }
    
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Product added to cart successfully!']);
        }
    
        return redirect()->route('show.cart.table')->with('success', 'Product added to cart successfully!');
    }
       
    public function addToCart($id)
    {
        $product = Product::findOrFail($id);

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $id)
                        ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $id,
                'quantity'   => 1,
                'price' => $product->offer_price,
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Product added to cart successfully!']);
        }

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function removeCart(Request $request)
    {
        $cartItem = Cart::find($request->cart_id);

        if($cartItem) {
            $cartItem->delete();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }

    public function clearCart()
    {
        Cart::where('user_id', Auth::id())->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Cart cleared successfully']);
        }

        return redirect()->back()->with('success', 'Cart cleared successfully');
    }
    public function increaseQty(Request $request, $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            session()->flash('error', 'Cart item not found');
            return response()->json(['status' => 'error', 'message' => 'Cart item not found']);
        }

        $product = Product::find($cart->product_id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found']);
        }

        if ($product->quantity <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Out of stock']);
        }

        if ($cart->quantity + 1 > $product->quantity) {
            return response()->json(['status' => 'error', 'message' => 'Not enough stock']);
        }

        $cart->quantity += 1;
        $cart->save();

        $subtotal = $cart->quantity * $cart->price;
        session()->flash('success', 'Quantity updated successfully');
        return response()->json([
            'status' => 'success',
            'new_qty' => $cart->quantity,
            'message' => 'Quantity updated successfully',
            'product_stock' => $product->quantity,
            'subtotal' => number_format($subtotal, 2)
        ]);
    }

    public function decreaseQty(Request $request, $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found'
            ]);
        }

        $product = Product::find($cart->product_id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ]);
        }

        if ($cart->quantity <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimum quantity is 1'
            ]);
        }

        $cart->quantity -= 1;
        $cart->save();

        $subtotal = $cart->quantity * $cart->price;

        return response()->json([
            'status' => 'success',
            'new_qty' => $cart->quantity,
            'product_stock' => $product->quantity,
            'subtotal' => number_format($subtotal, 2)
        ]);
    }

    public function increaseCartQty($id){
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['status' => 'error', 'message' => 'Cart item not found']);
        }

        $product = Product::find($cart->product_id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found']);
        }

        if ($product->quantity <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Out of stock']);
        }

        if ($cart->quantity + 1 > $product->quantity) {
            return response()->json(['status' => 'error', 'message' => 'Not enough stock']);
        }

        $cart->quantity += 1;
        $cart->save();
        return response()->json([
            'status' => 'success',
            'new_qty' => $cart->quantity,
        ]);
    }

    public function decreaseCartQty($id){
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found'
            ]);
        }

        $product = Product::find($cart->product_id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ]);
        }

        if ($cart->quantity <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimum quantity is 1'
            ]);
        }

        $cart->quantity -= 1;
        $cart->save();

        return response()->json([
            'status' => 'success',
            'new_qty' => $cart->quantity,
        ]);
    }
    public function getCart()
    {
        $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();

        return response()->json($cartItems);
    }
    public function applyCoupon(Request $request)
    {
        $couponCode = $request->input('coupon');
        $coupon = Coupon::where('code', $couponCode)->first();

        if ($coupon) {
            session(['coupon' => $coupon]);
             return response()->json([
                'status' => 'success',
                'message' => 'Coupon applied successfully!',
            ]);
        } else {
             return response()->json([
                'status' => 'error',
                'message' => 'Coupon applied Failed!',
            ]);
        }
    }  
    
    
    public function removeCoupon(Request $request)
    {
        $request->session()->forget('coupon');
        return response()->json([
                'status' => 'success',
                'message' => 'Coupon removed successfully!',
            ]);
    }
    
    public function proceed_to_checkout(Request $request)
    {
        $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('show.cart.table');
        }
        $addresses = Address::with('cityDetail','stateDetail','countryDetail','shippingPrice')->where('user_id', auth()->id())->get();
        $primary_address = $addresses->firstWhere('is_default', 1);
        $countries = Country::where('id',101)->get();
        $milestones = MilestoneSetting::where('status', 1)->get();
        return view('frontend.product.checkout',compact('milestones','cartItems','countries','addresses','primary_address'));
    }
    
    public function send(Request $request)
    {
        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mail_subject' => $request->subject,
            'message' => $request->message
        ];

        Mail::to('info@webbitech.com')->send(new ContactFormMail($details));

        return back()->with('success', 'Your message has been sent successfully!');
    }  

    public function wrapUpdate(Request $request)
    {
        $userId = auth()->id();
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found'
            ]);
        }
        $giftWrapId = $request->gift_wrap_id;
        $cart->gift_wrap_id = $giftWrapId;
        $cart->save();

        $carts = Cart::where('user_id', $userId)->with('product')->get();

        $subtotal = 0;
        foreach ($carts as $item) {
            $subtotal += $item->product->offer_price * $item->quantity;
        }
        $giftWrap = GiftWrap::find($giftWrapId);
        $giftWrapPrice = $giftWrap ? $giftWrap->price : 0;

        $newTotal = $subtotal + $giftWrapPrice;

        return response()->json([
            'status' => 'success',
            'message' => 'Gift wrap updated successfully',
            'gift_wrap_id' => $giftWrapId,
            'subtotal' => $subtotal,
            'gift_wrap_price' => $giftWrapPrice,
            'new_total' => $newTotal
        ]);
    }
    public function wrapMessageUpdate(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'gift_message' => 'required|string|max:255',
        ]);
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found',
            ]);
        }

        $cart->gift_message = $request->gift_message;
        $cart->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Gift message saved successfully.',
        ]);
    }
    public function removeMessageUpdate(Request $request)
    {
        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found',
            ]);
        }

        $cart->gift_wrap_id = null;
        $cart->gift_message = null;
        $cart->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Gift message saved successfully.',
        ]);
    }

    public function buyNow(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found']);
        }

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $product->id)
                        ->first();
        if ($cartItem) {
            $cartItem->quantity = $request->input('quantity', 1);
            $cartItem->save();
        } else {
            Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
                'quantity'   => $request->input('quantity', 1),
                'price' => $product->offer_price,
            ]);
        }

        return redirect()->route('product.proceed_to_checkout');
    }
}