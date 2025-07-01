<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ClassModel;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class StudentQualificationService
{
    // gpt-4o-mini
    public function evaluate(Student $student): array
    {
        $classes = ClassModel::all();
        $prompt = $this->buildPrompt($student, $classes);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an education advisor AI.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $message = $response->choices[0]->message->content ?? null;

            Log::info('AI raw message:', ['content' => $message]);

            if ($message) {
                // Try to find JSON inside the message if GPT adds any explanation around it
                preg_match('/\{.*\}/s', $message, $matches);
                $json = $matches[0] ?? null;

                if ($json) {
                    $decoded = json_decode($json, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('OpenAI Evaluation Error: ' . $e->getMessage());
        }

        return [
            'qualified' => false,
            'recommended_class' => null,
            'reason' => 'Unable to evaluate student due to an internal error.',
        ];
    }






private function buildPrompt(Student $student, $classes)
{
    $classList = $classes->map(fn($c) => "{$c->name} (Skill: {$c->skill_required}, Age: {$c->age_min}-{$c->age_max})")->implode("\n");

        return <<<PROMPT
            You are an education advisor AI.

            A student has applied to join a class. Based on the student's info and the list of available classes, decide:
            1. Is the student qualified?
            2. Which class is the best fit?
            3. Why?

            Student Info:
            - Age: {$student->age}
            - Skill Level: {$student->skill_level}
            - Math Score: {$student->math_score}

            Available Classes:
            $classList

            Respond strictly in the following JSON format:

            {
            "qualified": true,
            "recommended_class": "Algebra II",
            "reason": "The student is 14 and has intermediate skill level, matching Algebra II."
            }
            PROMPT;
}



}
