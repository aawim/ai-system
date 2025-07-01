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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->string('skill_level'); // beginner, intermediate, advanced
            $table->integer('math_score');
            $table->boolean('is_qualified')->default(false);
            $table->string('evaluation_reason')->nullable(); // beginner, intermediate, advanced
            $table->foreignId('recommended_class_id')->nullable()->constrained('class_models');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
