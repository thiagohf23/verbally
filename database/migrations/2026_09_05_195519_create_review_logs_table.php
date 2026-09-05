<?php

use App\Models\Flashcard;
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
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Flashcard::class)->constrained()->cascadeOnDelete()->index();
            $table->unsignedTinyInteger('rating');
            $table->unsignedInteger('interval_before');
            $table->unsignedInteger('interval_after');
            $table->decimal('ease_factor_before', 4, 2);
            $table->decimal('ease_factor_after', 4, 2);
            $table->dateTime('reviewed_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
