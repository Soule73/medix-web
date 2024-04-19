<?php

namespace App\Jobs;

use Exception;
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Enums\User\UserStatusEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Enums\Appointment\AppointmentStatusEnum;

class DeletePatientAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $appointments = $this->user->patient->appointments
                ->where("status", AppointmentStatusEnum::ACCEPTED)
                ->count();
            $patient_records = $this->user->patient->patient_records->count();

            // Delete review ratings (if any)
            $this->user->patient->reviewRatings->each(function ($rating) {
                $rating->delete();
            });

            if ($appointments === 0 && $patient_records === 0) {
                // Delete patient and user
                $this->user->patient->delete();
                $this->user->delete();
            } else {
                // Mark user as inactive
                $this->user->phone = null;
                $this->user->email = null;
                $this->user->status = UserStatusEnum::INACTIVE;
                $this->user->save();
            }

            // Delete notifications and tokens
            $this->user->notifications()->where('notifiable_id', $this->user->id)->delete();
            $this->user->tokens()->delete();
        } catch (Exception $e) {
            Log::error("Error DeletePatientAccount: " . $e->getMessage());
        }
    }
}
