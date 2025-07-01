<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SelectedStudentsExport implements FromCollection, WithHeadings
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Student::whereIn('id', $this->ids)
            ->select('name', 'age', 'skill_level', 'math_score', 'is_qualified', 'recommended_class_id', 'evaluation_reason')
            ->with('recommendedClass:id,name')
            ->get()
            ->map(function ($student) {
                return [
                    $student->name,
                    $student->age,
                    $student->skill_level,
                    $student->math_score,
                    $student->is_qualified ? 'Yes' : 'No',
                    optional($student->recommendedClass)->name ?? '-',
                    $student->evaluation_reason,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Age',
            'Skill Level',
            'Math Score',
            'Qualified',
            'Recommended Class',
            'Evaluation Reason',
        ];
    }
}
