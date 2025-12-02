<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HomeController extends Controller
{
    public function index(){
        $categories = Category::all();
        $productDatas = Product::inRandomOrder()->take(7)->get();
        $bestSells = Product::orderBy('sold', 'desc')->take(6)->get();
        return view('frontend.home',compact('categories','productDatas','bestSells'));
    }
    public function logout(){
        Session::flush();
        Auth::logout();
        return redirect()->route("home")->with(['success'=>true, 'message'=>'Sucessfully logged out.']);
    }
    public function userfront(){
        if(Auth::check()){
            // $ut = User::findOrFail(Auth::user()->id);
            $ut=Auth::user()->user_type;
            if($ut==0){
                return redirect()->route('admin.dashboard')->with(['success'=>true, 'message'=>'Sucessfully logged in.']);
            }
            if($ut==1){
                return redirect()->route('merchant.dashboard')->with(['success'=>true, 'message'=>'Sucessfully logged in.']);
            }
            if($ut==2){
                return redirect()->route('user.dashboard')->with(['success'=>true, 'message'=>'Sucessfully logged in.']);
            }
        }
        else{
            Session::flush();
            Auth::logout();
            return redirect()->route('home')->with(['warning'=> true,'message'=>'Unauthorized access attempt.']);
        }
    }
    public function user_profile(){
        return view('frontend.profile');
    }
    public function dashboard(){
        return view('frontend.profile');
    }
    public function order(){
        $user = Auth::user();
        $orders = $user->orders;
        return view('frontend.profile',compact('orders'));
    }
    public function order_detail($order_tracking_id){
        $order = Order::where('order_tracking_id', $order_tracking_id)->first();
        if (!$order) {
            return redirect()->back()->with(['error' => true, 'message' => 'Order not found']);
        }
        $orderItems = $order->orderItems()
            ->with('product')
            ->get();
        return view('frontend.profile',compact('order','orderItems'));
    }
    public function setting(){
        return view('frontend.profile');
    }

    public function address(){
        $user = get_address();
        return view('frontend.profile',compact('user'));
    }
    public function address_edit(){
        $user = get_address();
        return view('frontend.profile',compact('user'));
    }
    public function address_store(Request $request){
        save_address($request);
        return redirect()->route('user.dashboard')->with(['success'=>true, 'message'=>'Address updated successfully.']);
    }

    public function chatbot()
    {
       return view('frontend.chatbot');
    }
}
