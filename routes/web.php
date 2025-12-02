<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\MapController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\RatingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\MerchantController;
use App\Http\Controllers\Frontend\EsewaController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\ChatController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

/********************************** Public Route ****************************************/
route::get('/userchecking',[HomeController::class,'userfront'])->name('check.role');
route::get('/merchant-register', function(){
    return view('auth.merchant_register');
})->name('merchant.register')->middleware('logout');
route::get('/logout',[HomeController::class,'logout'])->name('user.logout');
// route::post('/login',[LoginController::class,'authenticate'])->name('login');
route::post('/register',[RegisterController::class,'authenticate'])->name('custom.register');
route::post('/change-password',[PasswordController::class, 'update'])->name('change.password');
route::post('/store-latlng',[MapController::class,'store'])->name('store.latlng');

/********************************** Customer Route ****************************************/

route::group([], function(){
    route::get('/',[HomeController::class, 'index'])->name('home');
    // Route to show the chat widget page
    Route::get('/chatbot', [ChatController::class, 'showWidget'])->name('chatbot');

    // Route to handle BotMan messages (widget endpoint)
    Route::match(['get', 'post'], '/botman', [ChatController::class, 'handle'])->name('botman.handle');
    route::group(['middleware'=>['auth', 'customer']], function(){
        route::get('/profile',[HomeController::class,'user_profile'])->name('user.profile');
        route::get('/dashboard',[HomeController::class,'dashboard'])->name('user.dashboard');
        route::get('/order',[HomeController::class,'order'])->name('user.order');
        route::get('/order/{id}',[HomeController::class,'order_detail'])->name('user.order.detail');
        route::get('/setting',[HomeController::class,'setting'])->name('user.setting');
        route::get('/address',[HomeController::class, 'address'])->name('user.address');
        route::get('/address/edit',[HomeController::class, 'address_edit'])->name('user.address.edit');
        route::post('/address/store',[HomeController::class,'address_store'])->name('user.address.store');
        route::get('/payment', [EsewaController::class, 'initiatePayment'])->name('payment');
        route::get('/payment/callback', [EsewaController::class, 'paymentCallback'])->name('payment.callback'); 
        route::get('/esewa',[EsewaController::class,'esewa_view'])->name('esewa.view');
    });
    
    route::group(['middleware'=> 'customer'],function(){
        route::get('/cart',[CartController::class,'index'])->name('cart.index');
        route::post('/add-to-cart', [CartController::class,'addToCart'])->name('cart.add');
        route::post('/cart/update/{id}',[CartController::class,'update'])->name('cart.update');
        route::get('/delete/{id}',[CartController::class,'delete'])->name('cart.delete');
        route::get('/delete-all',[CartController::class,'delete_all'])->name('cart.delete.all');
        route::get('/checkout',[OrderController::class, 'checkout'])->name('order.checkout');
        route::post('/order',[OrderController::class, 'order'])->name('order');
    });
    route::group(['prefix' => 'search'], function(){
        route::get('/search', [SearchController::class, 'search'])->name('search');
        route::get('/remove-search', [SearchController::class, 'remove_search'])->name('remove.search');
        route::get('/result-page',[SearchController::class, 'result_page'])->name('result.page');
    });
    route::get('/{category:slug}',[ProductController::class, 'list_product'])->name('product.list');
    route::group(['prefix' => 'product'], function () {
        route::get('/list',[ProductController::class, 'list_product'])->name('products.list');
        route::post('/rating/create',[RatingController::class,'creation'])->name('products.rating.create');
        route::get('/details/{product:slug}',[ProductController::class, 'product_detail'])->name('product.detail');
        route::group(['middleware' => 'auth'], function(){
            route::post('/order',[ProductController::class,'order'])->name('product.order');
            route::post('/buy',[ProductController::class, 'buy_now'])->name('buy.now');
        });
    });
});

/*********************************** Admin Route ******************************************/
route::group(['prefix'=>'admin','middleware'=>'admin'],function(){
    route::get('/dashboard',[AdminController::class,'index'])->name('admin.dashboard');
    route::group(['prefix'=> 'merchant'],function(){
        route::get('/new-merchant',[AdminController::class,'list_merchant'])->name('admin.merchant.new');
        route::get('/new-list',[AdminController::class,'new_list'])->name('admin.merchant.new.list');
        route::post('/new-list/status-update/{id}',[AdminController::class, 'status_update'])->name('admin.merchant.new.update');
        route::get('/new_merchant/delete/{id}',[AdminController::class,'new_merchant_delete'])->name('admin.merchant.new.delete');
        route::get('/verified-merchant',[AdminController::class, 'verify_merchant'])->name('admin.merchant.verify');
        route::get('/verified-list',[AdminController::class,'verified_list'])->name('admin.merchant.verified.list');
        route::get('/verified_merchant/delete/{id}',[AdminController::class,'verified_merchant_delete'])->name('admin.merchant.verified.delete');
    });
    route::group(['prefix'=> 'customer'],function(){
        route::get('/list_user',[AdminController::class,'list_customer'])->name('admin.customer.list');
        route::get('/delete/user/{id}',[AdminController::class,'delete_customer'])->name('admin.customer.delete');
    });
    route::get('/categories',[AdminController::class, 'category'])->name('admin.category');
    route::get('/view-categories',[AdminController::class, 'view_category'])->name('admin.category.view');
    route::post('/add-category',[AdminController::class, 'add_category'])->name('admin.category.add');
    route::get('/edit-category/{id}',[AdminController::class, 'edit_category'])->name('admin.category.edit');
    route::post('/update-category/{id}',[AdminController::class, 'update_category'])->name('admin.category.update');
    route::get('/delete-category/{id}',[AdminController::class, 'delete_category'])->name('admin.category.delete');
    route::get('/order',[AdminController::class,'order'])->name('admin.order');
    route::get('/order/{id}',[AdminController::class,'order_detail'])->name('admin.order.detail');
    route::post('/update-status/{id}',[AdminController::class,'order_status'])->name('admin.order.status');
    route::get('/addres',[AdminController::class,'address'])->name('admin.address');
    route::get('/address/create',[AdminController::class, 'address_edit'])->name('admin.address.edit');
    route::post('/address/store',[AdminController::class,'address_store'])->name('admin.address.store');
    route::get('/address/path/{id}',[AdminController::class,'address_path'])->name('admin.address.path');
    route::get('/product/address/{id}',[AdminController::class,'product_address_path'])->name('admin.product.address.path');
    route::get('/order/path/process/{id}',[AdminController::class,'address_path_test'])->name('admin.address.path.process');
});

/*********************************** Merchant Route ******************************************/
route::group(['prefix'=>'merchant','middleware'=>'merchant'],function(){
    route::get('/dashboard',[MerchantController::class,'index'])->name('merchant.dashboard');
    route::group(['prefix'=>'product'],function(){
        route::get('/new-product',[MerchantController::class,'list_product'])->name('merchant.product.new');
        route::get('/view-product',[MerchantController::class, 'view_product'])->name('merchant.product');
        route::post('/add-product', [MerchantController::class, 'add_product'])->name('product.add');
        route::get('/edit-product/{id}',[MerchantController::class, 'edit_product'])->name('product.edit');
        route::post('/update-product/{id}',[MerchantController::class, 'update_product'])->name('product.update');
        route::get('/delete-product/{id}', [MerchantController::class, 'delete_product'])->name('product.delete');
    });
    route::get('/order',[MerchantController::class, 'order'])->name('merchant.order');
    route::get('/order/{id}',[MerchantController::class,'order_detail'])->name('merchant.order.detail');
    route::post('/update-status/{id}',[MerchantController::class,'order_status'])->name('merchant.order.status');
    route::get('/rating',[RatingController::class,'list_rating'])->name('merchant.rating');
    route::post('/rating/update/{id}',[RatingController::class,'update_status'])->name('merchant.rating.update');
    route::get('/addres',[MerchantController::class,'address'])->name('merchant.address');
    route::get('/address/creat',[MerchantController::class, 'address_edit'])->name('merchant.address.edit');
    route::post('/address/store',[MerchantController::class,'address_store'])->name('merchant.address.store');
});


// Route::get('/chatbot', function () {
//     dd('here');
//     return view('frontend.chatbot');
// });

