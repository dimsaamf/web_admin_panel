<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiController;

Route::get('/', [TransaksiController::class, 'index'])->name('transaksi.index');
Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
Route::get('/transaksi/get-products', [TransaksiController::class, 'getProducts'])->name('transaksi.getProducts');
Route::post('/transaksi/get-product', [TransaksiController::class, 'getProductDetails'])->name('transaksi.getProductDetails');