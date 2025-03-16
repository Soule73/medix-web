<?php

use App\Enums\User\UserRoleEnum;
use App\Http\Controllers\DownloadPrescriptionController;
use App\Http\Controllers\DownloadTimetableController;
use App\Http\Controllers\PdfController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role === UserRoleEnum::ADMIN) {
        return redirect()->secure('/admin/login');
    } else {
        return redirect()->secure('/doctor/login');
    }
});
Route::get('/verification/email/send', function () {
    return view('verification-link-send');
    // if (auth()->check() && auth()->user()->role === UserRoleEnum::ADMIN) {
    //     return redirect()->secure('/admin/login');
    // } else {
    //     return redirect()->secure('/doctor/login');
    // }
})->name('verification-link-send');

Route::get('print-time-table', DownloadTimetableController::class)
    ->name('print-time-table')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('patient/prescription/{prescription_id}', [PdfController::class, 'viewPrescription'])
        ->name('view-prescription');
    Route::get('patient/prescription/{prescription_id}/stream', DownloadPrescriptionController::class)
        ->name('print-prescription');
});

Route::get('login', function () {
    return redirect('doctor\login');
})->name('login');

Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $user = User::findOrFail($request->id);
    if (is_null($user->email_verified_at)) {
        $user->email_verified_at = now();
        $user->save();
        $user->doctor()->create([]);
    }

    if (! Auth::loginUsingId($user->id)) {
        abort(403);
    }

    return redirect()->to('/doctor/login');
    // return $customerService->setValidatedEmail($request);
})->name('verification.verify');
