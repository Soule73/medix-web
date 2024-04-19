<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\ReviewRating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewRatingsResource;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewRatingApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            return ReviewRatingsResource::collection(
                $request->user()->patient->reviewRatings()->paginate()
            );
        } catch (Exception $e) {
            $code = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $code = $e->getStatusCode();
            }
            throw new HttpResponseException(response()->json($e->getMessage(), $code));
        }
    }

    public function store(Request $request)
    {

        try {
            $request->validate([
                "appointment_id" => ["required"],
                "patient_id" => ["required"],
                "doctor_id" => ["required"],
                "star" => ["required"],
                "comment" => ["nullable", "string"]
            ]);

            $new = ReviewRating::create($request->all());

            return response()->json($new);
        } catch (\Illuminate\Validation\ValidationException $validator) {
            throw new HttpResponseException(response()->json($validator->getMessage(), 422));
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json($e->getMessage(), 500));
        }
    }

    public function update(Request $request, string $id)
    {

        try {
            $request->validate([
                // "appointment_id" => ["required"],
                "patient_id" => ["required"],
                "doctor_id" => ["required"],
                "star" => ["required"],
                "comment" => ["nullable", "string"]
            ]);

            ReviewRating::findOrFail($id)->update($request->all());

            return response()->json(ReviewRating::findOrFail($id));
        } catch (\Illuminate\Validation\ValidationException $validator) {
            throw new HttpResponseException(response()->json($validator->getMessage(), 422));
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json($e->getMessage(), 500));
        }
    }
    public function delete(string $id)
    {
        try {
            ReviewRating::findOrFail($id)->delete();

            return response()->json(["success" => true]);
        } catch (Exception $e) {
            throw new HttpResponseException(response()->json(["success" => false], 500));
        }
    }
}
