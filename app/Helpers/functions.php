<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;

use Illuminate\Support\Str;
function create_slug($title){

    $slug = Str::slug($title,'-');

    $allSlugs =  Category::select('slug')->where('slug', 'like', $slug.'%')
            ->where('id', '<>', 0)
            ->get();

    //if not used the slug before
    if (! $allSlugs->contains('slug', $slug)){
        return $slug;
    }

    //if the slug is used before
    $i=1;
    while($i <= 10){
        $newSlug = $slug.'-'.$i;
        if (! $allSlugs->contains('slug', $newSlug)) {
            return $newSlug;
        }
        $i++;
    }
    return $slug;
}
function find_product($id){
    return Product::find($id);
}
function create_order($request){
    $order = new Order;
    $order->user_id = Auth::user()->id;
    $order->billing_name= $request->name;
    $order->order_tracking_id = 'ot-'.date("U");
    $order->billing_address = $request->address;
    $order->latitude = $request->latitude;
    $order->longitude = $request->longitude;
    $order->billing_email = $request->email;
    $order->payment = $request->payment;
    $order->shipping_cost = $request->shipping;
    $order->tax = $request->tax;
    
    // Calculate total based on shipping cost, tax, and subtotal
    $order->subtotal = Cart::subtotal();
    $order->total = $order->subtotal + $order->shipping_cost + $order->tax;

    $order->delivery_status = 'pending';
    $order->save();
    foreach(Cart::content() as $cartItem) {
        $order->orderItems()->create([
            'product_id' => $cartItem->id,
            'quantity' => $cartItem->qty,
        ]);
        $products = Product::where('id', $cartItem->id)->first();
        $products->sold = $products->sold+$cartItem->qty;
        $products->save();
    }
    Cart::destroy();
}
function create_esewa_order(){
    $order_data = session()->get('order_data');
    $order = new Order;
    $order->user_id = $order_data['user_id'];
    $order->billing_name= $order_data['billing_name'];
    $order->order_tracking_id = 'ot-'.date("U");
    $order->billing_address = $order_data['billing_address'];
    $order->latitude = $order_data['latitude'];
    $order->longitude = $order_data['longitude'];
    $order->billing_email = $order_data['billing_email'];
    $order->payment = $order_data['payment'];
    $order->shipping_cost = $order_data['shipping_cost'];
    $order->tax = $order_data['tax'];
    $order->subtotal = $order_data['subtotal'];
    $order->total = $order_data['total'];
    $order->delivery_status = $order_data['delivery_status'];
    $order->save();
    foreach(Cart::content() as $cartItem) {
        $order->orderItems()->create([
            'product_id' => $cartItem->id,
            'quantity' => $cartItem->qty,
        ]);
        $products = Product::where('id', $cartItem->id)->first();
        $products->sold = $products->sold+$cartItem->qty;
        $products->save();
    }
    Cart::destroy();
    session()->forget('payment_data');
    session()->forget('order_data');
}
function create_signature($message){
    $secret = '8gBm/:&EnhH.1/q';
    $s = hash_hmac('sha256', $message, $secret, true);
    $hashInBase64 =  base64_encode($s); 
    return $hashInBase64;
}
function get_address(){
    $user_id = auth()->user()->id;
    $user = User::find($user_id);
    return $user;
}

function save_address($request){
    $user_id = auth()->user()->id;
    $user = User::find($user_id);
    $user->latitude = $request->latitude;
    $user->longitude = $request->longitude;
    $user->address = $request->address;
    $user->save();
}