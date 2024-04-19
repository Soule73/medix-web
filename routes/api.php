<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DoctorApiController;
use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\RegisteredController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ReviewRatingApiController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;

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
                    Route::post('/{id}', 'show')->name('doctor.show');
                });
            });

        // appointment route
        Route::controller(AppointmentController::class)
            ->prefix('appointment')
            ->group(function () {
                Route::post('/', 'index')->name('appointment.all');
                Route::post('/store', 'store')->name('appointment.store');
                Route::patch('/update/{id}', 'update')->name('appointment.update');
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

Route::post('user/register', RegisteredController::class);

Route::post('test', function () {

    // Coordonnées de l'utilisateur
    // 18.088945457940532, -15.968176894599264 IUP
    // 18.089488175382456, -15.96637114229834 ISCAE
    // 18.08564334386589, -15.974666964409305 Musée natianal
    // 18.074848511638955, -15.958123711150273 depot camara
    $userLatitude = '18.074848511638955';
    $userLongitude = '-15.958123711150273';
    $radius_of_the_earth = 6371; //représente le rayon moyen de la Terre en kilomètres
    // Requête pour sélectionner les lieux les plus proches
    // $nearestPlaces = DB::table('work_places')
    //     ->whereNotNull(['latitude', 'longitude'])
    //     ->select(DB::raw("*,
    //     ($radius_of_the_earth * acos(cos(radians($userLatitude))
    //     * cos(radians(latitude))
    //     * cos(radians(longitude) - radians($userLongitude))
    //     + sin(radians($userLatitude))
    //     * sin(radians(latitude)))) AS distance"))
    //     ->orderBy('distance', 'asc')
    //     ->get();



    //   {
    //     "id": 1,
    //     "name": "Clinique SDS",
    //     "address": "Capital Ilot G,Tevragh-zeina",
    //     "latitude": 18.088945457940532,
    //     "longitude": -15.968176894599264,
    //     "doctor_id": 1,
    //     "city_id": 1,
    //     "created_at": "2024-03-09 23:05:02",
    //     "updated_at": "2024-03-12 14:51:49",
    //     "distance": 1.8937587036112338
    //   }

    //       {
    //     "name": "Clinique SDS",
    //     "latitude": 18.088945457940532,
    //     "longitude": -15.968176894599264,
    //     "distance": 1.8937587036112338
    //   }
    $nearestPlaces = DB::table('work_places')
        ->selectRaw("*,
        ($radius_of_the_earth * acos(cos(radians(?))
        * cos(radians(latitude))
        * cos(radians(longitude) - radians(?))
        + sin(radians(?))
        * sin(radians(latitude)))) AS distance", [$userLatitude, $userLongitude, $userLatitude])
        ->havingRaw("distance < ?", [50]) // Vous pouvez ajuster la distance maximale ici
        ->orderBy('distance', 'asc')
        ->get();


    //


    return $nearestPlaces;
});
