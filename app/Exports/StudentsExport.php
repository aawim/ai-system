<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;

class StudentsExport implements FromCollection
{
    protected $studentIds;

    public function __construct(array $studentIds)
    {
        $this->studentIds = $studentIds;
    }

    public function collection()
    {
        return Student::whereIn('id', $this->studentIds)->get();
    }
}
