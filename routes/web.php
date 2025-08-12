<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YagoutPayController;
use Illuminate\Support\Facades\Log;




Route::get('/', function () {
    return view('welcome');
});



Route::post('/process-payment', [YagoutPayController::class, 'initiatePayment'])->name('payment.initiate');

// Optionally, a GET route to show a simple checkout page before processing
Route::get('/checkout', function () {
    return view('checkout'); // A simple view with a "Pay Now" button that POSTs to /process-payment
})->name('checkout');

// Callback routes to handle payment gateway responses
Route::post('/payment/callback/success', function () {
    // Handle successful payment callback data here
    $callbackData = request()->all();
    
    // Log or process the success callback data
    Log::info('Payment success callback received', $callbackData);
    
    // You can add your success logic here
    // For example: update order status, send confirmation email, etc.
    
    return response()->json(['status' => 'success', 'message' => 'Callback processed']);
})->name('payment.callback.success');

Route::post('/payment/callback/fail', function () {
    // Handle failed payment callback data here
    $callbackData = request()->all();
    
    // Log or process the failure callback data
    Log::info('Payment failure callback received', $callbackData);
    
    // You can add your failure logic here
    // For example: update order status, send failure notification, etc.
    
    return response()->json(['status' => 'error', 'message' => 'Callback processed']);
})->name('payment.callback.fail');

