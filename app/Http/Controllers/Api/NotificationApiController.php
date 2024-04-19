<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Resources\PatientNotificationRessource;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationApiController extends Controller
{

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $notifications = DatabaseNotification::where('notifiable_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return PatientNotificationRessource::collection($notifications);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
    public function update(string $notificationId, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            DatabaseNotification::findOrFail($notificationId)->markAsRead();
            $notifications = DatabaseNotification::where('notifiable_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return PatientNotificationRessource::collection($notifications);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
    public function destroy(string $notificationId, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            DatabaseNotification::findOrFail($notificationId)->delete();
            $notifications = DatabaseNotification::where('notifiable_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return PatientNotificationRessource::collection($notifications);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
}
