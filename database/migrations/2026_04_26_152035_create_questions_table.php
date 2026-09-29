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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->references('id')->on('module_lessons')->nullOnDelete();
            $table->text('question_text');
            $table->string('question_type'); // multiple_choice, true_false, enumeration, identification
            $table->json('options')->nullable(); // For MCQs/TrueFalse options
            $table->text('correct_answer')->nullable(); // For text-based or the correct key
            $table->integer('default_points')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
