<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clues', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->text('prompt')->comment('The clue read aloud (Jeopardy "answer" shown on the board).');
            $table->text('correct_response')->comment('The expected contestant response (Jeopardy "question").');
            $table->unsignedInteger('value');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clues');
    }
};
