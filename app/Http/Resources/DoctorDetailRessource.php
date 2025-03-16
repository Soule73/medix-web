<?php

namespace App\Http\Resources;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Doctor
 * @property mixed $grouped_working_hours
 * @property mixed $qualifications
 */

class DoctorDetailRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'qualifications' => collect($this->qualifications)->map(function ($qualification) {
                return [
                    'id' => $qualification->id,
                    'name' => $qualification->name,
                    'institute' => $qualification->institute,
                    'procurement_date' => $qualification->procurement_date,
                ];
            }),
            'working_hours' => collect($this->grouped_working_hours)->map(function ($dayGroup, $dayName) {
                return $dayGroup->map(function ($workingHour) {
                    return [
                        'id' => $workingHour->id,
                        'start_at' => $workingHour->start_at,
                        'end_at' => $workingHour->end_at,
                        'work_place_id' => $workingHour->work_place->id,
                        'work_place_name' => $workingHour->work_place->name,
                        'work_place_address' => $workingHour->work_place->address,
                        'work_place_latitude' => $workingHour->work_place->latitude,
                        'work_place_longitude' => $workingHour->work_place->longitude,
                    ];
                });
            }),
            'review_ratings' => collect($this->review_ratings)->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'star' => $rating->star,
                    'patient_id' => $rating->patient_id,
                    'patient_fullname' => $rating->patient->user_fullname,
                    'patient_avatar' => $rating->patient->user->avatar !== null && Storage::exists($rating->patient->user->avatar) ? Storage::url($rating->patient->user->avatar) : $rating->patient->user->avatar,
                    'doctor_id' => $rating->doctor->id,
                    'doctor_fullname' => $rating->doctor->user_fullname,
                    'doctor_avatar' => $rating->doctor->user->avatar !== null && Storage::exists($rating->doctor->user->avatar) ? Storage::url($rating->doctor->user->avatar) : $rating->doctor->user->avatar,
                    'comment' => $rating->comment,
                    'created_at' => $rating->created_at,
                    'updated_at' => $rating->updated_at,
                ];
            }),

        ];
    }
}
