<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPlacesLocationRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "address" => $this->address,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "doctor_id" => $this->doctor_id,
            "city_id" => $this->city_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "distance" => $this->distance,


            // 'doctor_id' => $this->doctor->id,
            'doctor_avatar' => $this->doctor->user->avatar !== null && Storage::disk('local')->exists($this->doctor->user->avatar) ? Storage::url($this->doctor->user->avatar) : $this->doctor->user->avatar,
            'doctor_fullname' => $this->doctor->user_fullname,
            'doctor_email' => $this->doctor->user->email,
            'doctor_phone' => $this->doctor->user->phone,
            'doctor_professional_title' => $this->doctor->professional_title,
            'specialities' => $this->doctor->specialities->pluck('name')->toArray(),

        ];
    }
}
