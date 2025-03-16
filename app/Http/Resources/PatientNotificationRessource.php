<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin DatabaseNotification
 *  */

class PatientNotificationRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'status' => $this->data['status'],
            'appointment_id' => $this->data['viewData']['record'],
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
