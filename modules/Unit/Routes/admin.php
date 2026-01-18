<?php

use Illuminate\Support\Facades\Route;
use Modules\Unit\Http\Controllers\Admin\UnitController;

Route::group(['middleware' => 'can:admin.units.index'], function () {
    Route::get('units', [UnitController::class, 'index'])->name('admin.units.index');
    Route::get('units/index/table', [UnitController::class, 'table'])->name('admin.units.table');
    Route::get('units/create', [UnitController::class, 'create'])->name('admin.units.create')->middleware('can:admin.units.create');
    Route::post('units', [UnitController::class, 'store'])->name('admin.units.store')->middleware('can:admin.units.create');
    Route::get('units/{id}/edit', [UnitController::class, 'edit'])->name('admin.units.edit')->middleware('can:admin.units.edit');
    Route::put('units/{id}', [UnitController::class, 'update'])->name('admin.units.update')->middleware('can:admin.units.edit');
    Route::delete('units/{ids?}', [UnitController::class, 'destroy'])->name('admin.units.destroy')->middleware('can:admin.units.destroy');
});
