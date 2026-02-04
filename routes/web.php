<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OutletSelectionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PricingSettingController;
use App\Http\Controllers\PricingTableController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $defaultRoute = $user->hasRole('CASHIER')
        ? route('pos.index')
        : url('/admin');

    return redirect()->to($defaultRoute);
});

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/outlets/select', [OutletSelectionController::class, 'index'])->name('outlets.select');
    Route::post('/outlets/select', [OutletSelectionController::class, 'select'])->name('outlets.select.submit');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:OWNER|ADMIN|MANAGER|CASHIER')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/add', [PosController::class, 'addItem'])->name('pos.add');
        Route::post('/pos/update', [PosController::class, 'updateItem'])->name('pos.update');
        Route::post('/pos/remove', [PosController::class, 'removeItem'])->name('pos.remove');
        Route::post('/pos/coupon', [PosController::class, 'applyCoupon'])->name('pos.coupon');
        Route::post('/pos/hold', [PosController::class, 'hold'])->name('pos.hold');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/receipts/{sale}', [ReceiptController::class, 'show'])->name('receipts.show');
        Route::post('/receipts/{sale}/email', [ReceiptController::class, 'email'])->name('receipts.email');
    });

    Route::middleware('role:OWNER|ADMIN|MANAGER|CASHIER')->group(function () {
        Route::resource('customers', CustomerController::class)->only(['create', 'store']);
    });

    Route::middleware('role:OWNER|ADMIN|MANAGER')->group(function () {
        Route::resource('products', ProductController::class)->except(['destroy']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update']);
        Route::resource('tags', TagController::class)->only(['index', 'store', 'update']);

        Route::get('/pricing-table', [PricingTableController::class, 'index'])->name('pricing.index');
        Route::resource('pricing-settings', PricingSettingController::class)->except(['show']);

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        Route::resource('customers', CustomerController::class)->except(['show', 'destroy', 'create', 'store']);

        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::post('/shifts/cash', [ShiftController::class, 'cashMovement'])->name('shifts.cash');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/sales/excel', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.excel');
        Route::get('/reports/inventory/excel', [ReportController::class, 'exportInventoryExcel'])->name('reports.inventory.excel');
        Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/inventory/pdf', [ReportController::class, 'exportInventoryPdf'])->name('reports.inventory.pdf');
    });

});

Route::get('/receipt/{token}', [ReceiptController::class, 'public'])->name('receipts.public');
