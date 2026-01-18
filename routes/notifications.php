<?php

use Illuminate\Support\Facades\Route;
use FleetCart\Http\Controllers\Admin\NotificationController;

Route::prefix('admin/api')->middleware(['web', 'admin'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
});
