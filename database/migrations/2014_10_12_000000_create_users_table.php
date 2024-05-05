<?php

use App\Enums\LangEnum;
use App\Enums\User\UserSexEnum;
use App\Enums\User\UserRoleEnum;
use App\Enums\User\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', UserStatusEnum::values())->default(UserStatusEnum::ACTIVE->value);
            $table->enum('role', UserRoleEnum::values())->default(UserRoleEnum::PATIENT->value);
            $table->string('one_signal_id')->nullable();
            $table->enum('sex', UserSexEnum::values())->default(UserSexEnum::MAN->value);
            $table->enum('default_lang', LangEnum::values())->default(LangEnum::FR->value);
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();

            $table->timestamps();
        });
        DB::statement('ALTER TABLE users ADD CONSTRAINT user_email_or_phone_not_null CHECK(email IS NOT NULL OR phone IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
