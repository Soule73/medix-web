<?php

use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;
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
        Schema::create('documents_for_validations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('application/pdf');
            $table->enum('status', DocumentsForValidationStatusEnum::values())
                ->default(DocumentsForValidationStatusEnum::Pending->value);
            $table->string('path');
            $table->text('message');

            $table->foreignId('doctor_id')->constrained('doctors')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_for_validations');
    }
};
