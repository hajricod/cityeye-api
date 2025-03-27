<?php

use App\Enums\AuthorizationLevel;
use App\Enums\CasePersonType;
use App\Enums\CaseRole;
use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Enums\UserRole;
use App\Enums\Gender;
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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('case_name', 255);
            $table->text('description')->nullable();
            $table->string('area', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->enum('case_type', array_column(CaseType::cases(), 'value'))->default(CaseType::Criminal);
            $table->enum('authorization_level', array_column(AuthorizationLevel::cases(), 'value'))->default(AuthorizationLevel::Low);
            $table->enum('case_status', array_column(CaseStatus::cases(), 'value'))->default(CaseStatus::Pending);

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('case_assignees', function (Blueprint $table) {

            $table->id();

            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('assigned_role', array_column(UserRole::cases(), 'value'))->default(UserRole::Officer);
            $table->enum('authorization_level', array_column(AuthorizationLevel::cases(), 'value'))->nullable();

            $table->timestamps();
        });

        Schema::create('case_persons', function (Blueprint $table) {

            $table->id();

            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->enum('type', array_column(CasePersonType::cases(), 'value'));
            $table->string('name');
            $table->integer('age')->nullable();
            $table->enum('gender', array_column(Gender::cases(), 'value'));
            $table->enum('role', array_column(CaseRole::cases(), 'value'))->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_persons');
        Schema::dropIfExists('case_assignees');
        Schema::dropIfExists('cases');
    }
};
