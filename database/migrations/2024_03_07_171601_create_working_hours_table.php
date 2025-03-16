<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->time('start_at');
            $table->time('end_at');

            $table->foreignId('work_place_id')
                ->constrained('work_places')
                ->cascadeOnDelete();

            $table->foreignId('day_id')->constrained('days');

            $table->foreignId('doctor_id')->constrained('doctors')
                ->cascadeOnDelete();

            $table->timestamps();
        });
        DB::statement('ALTER TABLE working_hours ADD CONSTRAINT working_hour_start_less_end_at CHECK(start_at < end_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
