<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     $classes = [
            [
                'name' => 'Math Basics',
                'description' => 'Intro to math for beginners.',
                'skill_required' => 'beginner',
                'age_min' => 8,
                'age_max' => 12,
            ],
            [
                'name' => 'Geometry Fundamentals',
                'description' => 'Understanding shapes and space.',
                'skill_required' => 'beginner',
                'age_min' => 10,
                'age_max' => 13,
            ],
            [
                'name' => 'Intermediate Algebra',
                'description' => 'Solving equations and expressions.',
                'skill_required' => 'intermediate',
                'age_min' => 12,
                'age_max' => 15,
            ],
            [
                'name' => 'Statistics and Probability',
                'description' => 'Intro to data and chance.',
                'skill_required' => 'intermediate',
                'age_min' => 13,
                'age_max' => 16,
            ],
            [
                'name' => 'Advanced Mathematics',
                'description' => 'Prep for university-level math.',
                'skill_required' => 'advanced',
                'age_min' => 15,
                'age_max' => 18,
            ],
            [
                'name' => 'Applied Calculus',
                'description' => 'Real-world calculus applications.',
                'skill_required' => 'advanced',
                'age_min' => 16,
                'age_max' => 18,
            ],
        ];

        foreach ($classes as $class) {
            ClassModel::create($class);
        }
    }
}
