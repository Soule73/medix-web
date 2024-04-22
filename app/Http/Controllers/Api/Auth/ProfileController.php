<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\PatientInfoRessource;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @throws Exception|HttpResponseException
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        try {
            $request->user()->fill($request->validated());
            $request->user()->patient()->update([
                'city_id' => $request->city_id,
                'addresse' => $request->addresse,
                'birthday' => Carbon::parse($request->birthday)->format('Y-m-d'),
            ]);

            $request->user()->save();

            $request->user()->patient()->update([
                'city_id' => $request->city_id,
                'addresse' => $request->addresse,
            ]);

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
     * update user oneSignal Id for notification
     *
     * @return JsonResponse
     *
     * @throws Exception|HttpResponseException
     */
    public function updateOneSignalId(Request $request)
    {
        try {
            $request->validate([
                'one_signal_id' => ['required'],
            ]);

            $request->user()->update(['one_signal_id' => $request->one_signal_id]);

            return response()->json(new PatientInfoRessource($request->user()));
        } catch (ValidationException $e) {
            return response()->json(__('doctor/api.somethin-weng-wrong'), 422);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * update user default lang in application and preference lang to send notification
     *
     * @return JsonResponse|HttpResponseException
     */
    public function updateDefaultLang(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'default_lang' => ['required'],
            ]);

            $request->user()->update(['default_lang' => $request->default_lang]);

            return response()->json(new PatientInfoRessource($request->user()));
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

    /**
     * Update user avatar
     *
     * @return JsonResponse|HttpResponseException
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate(
                ['avatar' => ['required', 'url']]
            );
            $request->user()->avatar = $request->avatar;
            $request->user()->save();

            return response()->json(new PatientInfoRessource($request->user()));
        } catch (ValidationException $e) {
            return response()->json(__('doctor/api.somethin-weng-wrong'), 422);
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    /**
     * update user current password
     *
     * @return JsonResponse|HttpResponseException
     */
    public function updatePassword(Request $request): JsonResponse
    {

        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults()],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json(__('doctor/api.password-updated'));
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
