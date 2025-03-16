<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\DoctorApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ReviewRatingApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->group(function () {

        // token route
        Route::controller(TokenController::class)
            ->prefix('auth/user')
            ->group(function () {
                Route::post('/', 'user');
                Route::delete('/delete', 'deleteAccount')->name('api.user.delete');
                Route::delete('/token/delete', 'destroy')->name('api.logout');
            });

        //auth route
        Route::controller(ProfileController::class)
            ->prefix('auth/user/update')
            ->group(function () {
                Route::patch('/', 'update');
                Route::patch('/one-signal-id', 'updateOneSignalId');
                Route::patch('/default-lang', 'updateDefaultLang');
                Route::patch('/avatar', 'updateAvatar');
                Route::patch('/password', 'updatePassword');
            });

        // doctor route
        Route::controller(DoctorApiController::class)
            ->group(function () {
                Route::post('auth/user/favoris', 'favoris')->name('doctor.favoris');

                Route::prefix('doctor')->group(function () {
                    Route::post('/', 'index')->name('doctor.all');
                    Route::post('/specialities', 'specialities')->name('specialities');
                    Route::post('/{id}', 'show')->name('doctor.details');
                    Route::post('/find/{id}', 'find')->name('doctor.find');
                });
                Route::post('/work-place-location', 'workPlcaLocation')->name('work-place-location');
            });

        // appointment route
        Route::controller(AppointmentController::class)
            ->prefix('appointment')
            ->group(function () {
                Route::post('/', 'index')->name('appointment.all');
                Route::post('/store', 'store')->name('appointment.store');
                Route::patch('/update/{id}', 'update')->name('appointment.update');
                Route::patch('/confirm-appoinment/{id}', 'confirmAppoinment')->name('appointment.rescheduleDate');
                Route::delete('/delete/{id}', 'destroy')->name('appointment.delete');
                Route::post('/{id}', 'show')->name('appointment.show');
            });

        // notifications route
        Route::controller(NotificationApiController::class)
            ->prefix('notification')
            ->group(function () {
                Route::post('/', 'index');
                Route::patch('/mark-as-read/{notificationId}', 'update');
                Route::delete('/delete/{notificationId}', 'destroy');
            });

        // review rating route
        Route::controller(ReviewRatingApiController::class)
            ->prefix('review-rating')
            ->group(function () {
                Route::post('/', 'index');
                Route::post('/store', 'store')->name('review-rating.store');
                Route::patch('/update/{id}', 'update')->name('review-rating.update');
                Route::delete('/delete/{id}', 'delete')->name('review-rating.delete');
            });
    });

Route::post('auth/token', [TokenController::class, 'store']);
Route::post('auth/verify', [TokenController::class, 'verify']);
Route::patch('auth/reset-password', [TokenController::class, 'resetPassword']);

Route::post('user/register', [TokenController::class, 'registerPatient']);
