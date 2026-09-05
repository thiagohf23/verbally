<?php

use App\Models\Deck;
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
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Deck::class)->constrained()->cascadeOnDelete()->index();
            $table->text('front');
            $table->text('back');
            $table->string('grammar_tag')->nullable()->index();
            $table->text('explanation')->nullable();
            $table->string('audio_text')->nullable();
            $table->unsignedInteger('interval_days')->default(0);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->unsignedInteger('repetitions')->default(0);
            $table->unsignedInteger('lapses')->default(0);
            $table->dateTime('due_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
