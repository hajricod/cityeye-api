<?php

use App\Enums\UserRole;
use App\Enums\CaseStatus;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('case_id')->nullable()->constrained('cases')->onDelete('set null');

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('civil_id')->nullable();
            $table->text('description', 1000);
            $table->enum('role', array_column(UserRole::cases(), 'value'))->default(UserRole::Citizen);
            $table->enum('case_status', array_column(CaseStatus::cases(), 'value'))->default(CaseStatus::Pending);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
