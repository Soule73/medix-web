<?php

use App\Enums\Appointment\AppointmentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->enum('status', AppointmentStatusEnum::values())
                ->default(AppointmentStatusEnum::PENDING->value);
            $table->string('motif')->nullable();
            $table->longText('accepted_message')->nullable();
            $table->longText('reason_for_refusal')->nullable();
            $table->timestamp('date_appointment');
            $table->timestamp('reschedule_date');
            $table->boolean('payed')->default(false);
            $table->boolean('add_by_doctor');
            $table->double('amount')->default(0);
            $table->double('discount')->default(0);
            $table->double('confirm_payed')->default(0);
            $table->double('remind_patient')->default(0);

            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->foreignId('work_place_id')->nullable()->constrained('work_places')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
