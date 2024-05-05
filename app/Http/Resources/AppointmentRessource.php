<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AppointmentRessource extends JsonResource
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
            'patient_id' => $this->patient_id,
            'date_appointment' => $this->date_appointment,
            'reschedule_date' => $this->reschedule_date,
            'add_by_doctor' => $this->add_by_doctor,
            'reason_for_refusal' => $this->reason_for_refusal,
            'accepted_message' => $this->accepted_message,
            'motif' => $this->motif,
            'status' => $this->status,
            'payed' => $this->payed,
            'amount' => sprintf('%.2f', $this->amount),
            'discount' => sprintf('%.2f', $this->discount),

            'doctor_id' => $this->doctor->id,
            'doctor_avatar' => $this->doctor->user->avatar !== null && Storage::disk('local')->exists($this->doctor->user->avatar) ? Storage::url($this->doctor->user->avatar) : $this->doctor->user->avatar,
            'doctor_fullname' => $this->doctor->user_fullname,
            'doctor_email' => $this->doctor->user->email,
            'doctor_phone' => $this->doctor->user->phone,
            'doctor_professional_title' => $this->doctor->professional_title,

            'work_place_id' => $this->work_place->id,
            'work_place_name' => $this->work_place->name,
            'work_place_address' => $this->work_place->address,
            'work_place_latitude' => $this->work_place->latitude,
            'work_place_longitude' => $this->work_place->longitude,

            'review-rating' => $this->reviewRating ? [
                'id' => $this->reviewRating->id,
                'star' => $this->reviewRating->star,
                'comment' => $this->reviewRating->comment,
                'created_at' => $this->reviewRating->created_at,
                'updated_at' => $this->reviewRating->updated_at,
            ] : null,
        ];
    }
}
