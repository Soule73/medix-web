<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DoctorRessource extends JsonResource
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
            'fullname' => $this->user_fullname,
            'visit_price' => sprintf('%.2f', $this->visit_price),
            'bio' => $this->bio,
            'professional_title' => $this->professional_title,
            'specialities' => $this->specialities->pluck('name')->toArray(),
            'phone' => $this->user->phone,
            'email' => $this->user->email,
            'sex' => $this->user->sex,
            'patients_count' => $this->patientsCount->first() ? $this->patientsCount->first()->aggregate : 0,
            'year_experience' => $this->year_experience,
            'ratings' => sprintf('%.1f', $this->review_ratings_avg_star),
            'avatar' => $this->user->avatar !== null && Storage::disk('local')->exists($this->user->avatar) ? Storage::url($this->user->avatar) : $this->user->avatar,
        ];
    }
}
