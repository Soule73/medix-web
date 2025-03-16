<?php

namespace App\Http\Resources;

use App\Models\WorkingHour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkingHour */
class WorkingHourRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'work_place_address' => $this->work_place->address,
            'work_place_name' => $this->work_place->name,
            'work_place_latitude' => $this->work_place->latitude,
            'work_place_longitude' => $this->work_place->longitude,
            'day' => $this->day->name,
        ];
    }
}
