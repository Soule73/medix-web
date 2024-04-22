<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\User\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTokenRequest;
use App\Http\Resources\PatientInfoRessource;
use App\Jobs\DeletePatientAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class TokenController extends Controller
{
    /**
     * store new token for user
     *
     * @throws Exception|HttpResponseException
     */
    public function store(StoreTokenRequest $request): JsonResponse
    {
        try {
            $user = User::where('phone', '=', $request->phone)
                ->where('status', '=', UserStatusEnum::ACTIVE->value)
                ->whereDate('phone_verified_at', '<=', now())
                ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(__('doctor/api.incorrect-auth-info'), 401);
            }

            $token = $user->createToken($request->device_id)->plainTextToken;

            return response()->json(['token' => $token]);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * get current user informations from storage
     *
     * @throws Exception|HttpResponseException
     */
    public function user(Request $request): JsonResponse
    {
        try {

            return response()->json(new PatientInfoRessource($request->user()));
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * destroy current access token
     *
     * @throws Exception|HttpResponseException
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json('', 204);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * delete user account permanent if don't have any
     * appointment in application else set user
     * status to inactive
     *
     * @throws Exception|HttpResponseException
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            DeletePatientAccount::dispatch(user: $request->user());

            return response()->json('', 204);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {

        try {
            $validated = $request->validate([
                'phone' => ['required'],
                'password' => ['required', Password::defaults()],
                'device_id' => 'required',

            ]);

            $user = User::where('phone', $validated['phone'])
                ->where('status', UserStatusEnum::ACTIVE->value)
                ->whereDate('phone_verified_at', '<=', now())
                ->first();
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken($request->device_id)->plainTextToken;

            return response()->json(['token' => $token]);
        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), 422);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    public function verify(Request $request): JsonResponse
    {

        try {
            $request->validate([
                'phone' => ['required'],
            ]);

            $user = User::where('phone', '=', $request->phone)
                ->where('status', '=', UserStatusEnum::ACTIVE->value)
                ->whereDate('phone_verified_at', '<=', now())
                ->first();

            if (! $user) {
                return response()->json(['success' => false]);
            }

            return response()->json(['success' => true]);
        } catch (ValidationException $e) {
            return response()->json($e->getMessage(), 422);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }
}
