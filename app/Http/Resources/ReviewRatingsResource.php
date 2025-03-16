<?php

namespace App\Http\Resources;

use App\Models\ReviewRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReviewRating */

class ReviewRatingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'star' => $this->star,
            'comment' => $this->comment,
            'patient_id' => $this->patient_id,
            'patient_fullname' => $this->patient->user->fullname,
            'patient_avatar' => $this->patient->user->avatar !== null && Storage::exists($this->patient->user->avatar) ? Storage::url($this->patient->user->avatar) : $this->patient->user->avatar,
            'doctor_id' => $this->doctor->id,
            'doctor_fullname' => $this->doctor->user->fullname,
            'doctor_avatar' => $this->doctor->user->avatar !== null && Storage::exists($this->doctor->user->avatar) ? Storage::url($this->doctor->user->avatar) : $this->doctor->user->avatar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
