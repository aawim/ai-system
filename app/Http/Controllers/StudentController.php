<?php

namespace App\Http\Controllers;

use App\Models\Student;

use App\Models\ClassModel;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\StudentQualificationService;

class StudentController extends Controller
{
    public function index()
    {
        return Student::with('recommendedClass')->get();
    }

    public function store(Request $request)
    {
        $student = Student::create($request->all());
        return response()->json($student);
    }

    public function evaluate($id)
    {
        $student = Student::findOrFail($id);

        $ai = new StudentQualificationService(); // Don't call buildPrompt() here directly

        $result = $ai->evaluate($student);

        $student->is_qualified = $result['qualified'];

        $class = ClassModel::where('name', $result['recommended_class'])->first();
        if ($class) {
            $student->recommended_class_id = $class->id;
        }

        // Optional: save the reason if your DB has the column
        $student->evaluation_reason = $result['reason'] ?? null;

        $student->save();

        return response()->json([
            'student' => $student->load('recommendedClass'),
            'recommendation' => $result,
            'qualified' => $student->is_qualified,
            'recommended_class' => $student->recommendedClass->name ?? null,
            'reason' => $student->evaluation_reason
        ]);
    }

    public function evaluateBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $students = Student::whereIn('id', $ids)->get();

        $results = [];
        $ai = new StudentQualificationService();

        foreach ($students as $student) {
            $result = $ai->evaluate($student);

            $student->is_qualified = $result['qualified'];
            $student->evaluation_reason = $result['reason'] ?? null;

            if (!empty($result['recommended_class'])) {
                $class = ClassModel::where('name', $result['recommended_class'])->first();
                if ($class) {
                    $student->recommended_class_id = $class->id;
                }
            }

            $student->save();

            $results[] = [
                'student_id' => $student->id,
                'qualified' => $student->is_qualified,
                'recommended_class' => $student->recommendedClass->name ?? null,
                'reason' => $student->evaluation_reason
            ];
        }

        return response()->json(['results' => $results]);
    }
}
