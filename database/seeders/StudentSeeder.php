<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       for ($i = 1; $i <= 20; $i++) {
            $skillLevel = Arr::random(['beginner', 'intermediate', 'advanced']);
            $age = rand(10, 18);
            $mathScore = rand(30, 100);
            $isQualified = $mathScore >= 60;

            // Find matching classes
            $matchingClasses = ClassModel::where('skill_required', $skillLevel)
                ->where('age_min', '<=', $age)
                ->where('age_max', '>=', $age)
                ->get();

            $recommendedClassId = ($isQualified && $matchingClasses->isNotEmpty())
                ? $matchingClasses->random()->id
                : null;

            Student::create([
                'name' => 'Student ' . $i,
                'age' => $age,
                'skill_level' => $skillLevel,
                'math_score' => $mathScore,
                'is_qualified' => $isQualified,
                'recommended_class_id' => $recommendedClassId,
            ]);
        }
    }
}
