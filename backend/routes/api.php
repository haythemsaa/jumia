<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ComparisonController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Products - Public
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/{id}/related', [ProductController::class, 'related']);

// Categories - Public
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Vendors - Public
Route::get('/vendors', [VendorController::class, 'index']);
Route::get('/vendors/{id}', [VendorController::class, 'show']);

// Reviews - Public (read-only)
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

// Payment webhooks - Public (called by payment gateways)
Route::post('/payment/webhook/edinar', [PaymentController::class, 'edinarWebhook'])->name('payment.webhook.edinar');
Route::post('/payment/webhook/konnect', [PaymentController::class, 'konnectWebhook'])->name('payment.webhook.konnect');
Route::get('/payment/success/{order}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/fail/{order}', [PaymentController::class, 'paymentFailed'])->name('payment.fail');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Products - Vendor only
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markAsHelpful']);
    Route::get('/reviews/user', [ReviewController::class, 'userReviews']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Returns
    Route::get('/returns', [ReturnController::class, 'index']);
    Route::post('/returns', [ReturnController::class, 'store']);
    Route::get('/returns/{id}', [ReturnController::class, 'show']);
    Route::put('/returns/{id}/cancel', [ReturnController::class, 'cancel']);

    // Payments
    Route::post('/payment/initiate', [PaymentController::class, 'initiate']);
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);
    Route::get('/payment/history', [PaymentController::class, 'paymentHistory']);

    // Invoices
    Route::get('/invoices/{order}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('/invoices/{order}/view', [InvoiceController::class, 'view'])->name('invoice.view');
    Route::post('/invoices/{order}/email', [InvoiceController::class, 'email']);
    Route::get('/invoices/{order}/url', [InvoiceController::class, 'getUrl']);

    // Product Comparison
    Route::get('/comparison', [ComparisonController::class, 'index']);
    Route::post('/comparison/add', [ComparisonController::class, 'add']);
    Route::delete('/comparison/remove/{productId}', [ComparisonController::class, 'remove']);
    Route::delete('/comparison/clear', [ComparisonController::class, 'clear']);
    Route::get('/comparison/compare', [ComparisonController::class, 'compare']);
    Route::get('/comparison/check/{productId}', [ComparisonController::class, 'check']);

    // Chat
    Route::get('/chat/conversations', [ChatController::class, 'index']);
    Route::post('/chat/conversations', [ChatController::class, 'getOrCreate']);
    Route::get('/chat/conversations/{id}', [ChatController::class, 'show']);
    Route::post('/chat/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::put('/chat/conversations/{id}/read', [ChatController::class, 'markAsRead']);
    Route::put('/chat/conversations/{id}/close', [ChatController::class, 'close']);
    Route::get('/chat/unread', [ChatController::class, 'unreadCount']);
    Route::delete('/chat/messages/{id}', [ChatController::class, 'deleteMessage']);

    // Vendor routes
    Route::prefix('vendor')->middleware('vendor')->group(function () {
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        Route::get('/products', [VendorController::class, 'products']);
        Route::get('/orders', [VendorController::class, 'orders']);
        Route::put('/orders/{id}/status', [VendorController::class, 'updateOrderStatus']);

        // Vendor review responses
        Route::post('/reviews/{id}/respond', [ReviewController::class, 'respond']);
    });

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        // Review moderation
        Route::get('/reviews/pending', [ReviewController::class, 'pending']);
        Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
        Route::put('/reviews/{id}/reject', [ReviewController::class, 'reject']);

        // Return management
        Route::get('/returns', [ReturnController::class, 'adminIndex']);
        Route::put('/returns/{id}/approve', [ReturnController::class, 'approve']);
        Route::put('/returns/{id}/reject', [ReturnController::class, 'reject']);
        Route::put('/returns/{id}/processing', [ReturnController::class, 'markAsProcessing']);
        Route::put('/returns/{id}/complete', [ReturnController::class, 'complete']);
        Route::get('/returns/statistics', [ReturnController::class, 'statistics']);
    });
});
