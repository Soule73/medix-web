<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */

class PatientInfoRessource extends JsonResource
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
            'patien_id' => $this->patient->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'firstName' => $this->first_name,
            'sex' => $this->sex,
            'role' => $this->role,
            'status' => $this->status,
            'default_lang' => $this->default_lang->value,
            'avatar' => $this->avatar !== null && $this->avatar !== null && Storage::disk('local')->exists($this->avatar) ? Storage::url($this->avatar) : $this->avatar,
            'fullname' => $this->fullname,
            'id_cnss' => $this->patient->id_cnss,
            'addresse' => $this->patient->addresse,
            'birthday' => $this->patient->birthday,
            'city_id' => $this->patient->city_id,
            'city_name' => $this->patient->city->name,
            'one_signal_id' => $this->one_signal_id,
            'phone_verified_at' => $this->phone_verified_at,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->created_at,
        ];
    }
}
