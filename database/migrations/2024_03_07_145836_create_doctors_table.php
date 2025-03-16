<?php

use App\Enums\Doctor\DoctorStatusEnum;
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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('professional_title')->default('Dr.');
            $table->enum('status', DoctorStatusEnum::values())
                ->default(DoctorStatusEnum::NOTVALIDATED->value);
            $table->string('bio')->nullable();
            $table->decimal('visit_price')->default(0);

            $table->foreignId('user_id')->unique()->constrained('users');

            $table->timestamps();
        });
        DB::statement('ALTER TABLE doctors ADD CONSTRAINT doctor_visit_price CHECK(visit_price >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
