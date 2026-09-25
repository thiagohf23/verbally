<?php

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
        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('kind')->default('conversation')->index();
            $table->string('scenario_key')->nullable()->index();
            $table->string('feedback_mode')->default('fluency');
            $table->string('status')->default('active')->index();
            $table->json('messages')->nullable();
            $table->json('feedback')->nullable();
            $table->unsignedInteger('turns_count')->default(0);
            $table->unsignedInteger('replay_count')->default(0);
            $table->unsignedInteger('speaking_seconds')->default(0);
            $table->timestamp('started_at')->useCurrent()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
