<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public function recommendedClass()
    {
        return $this->belongsTo(ClassModel::class, 'recommended_class_id');
    }
}
