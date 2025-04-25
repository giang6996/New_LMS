<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    public function enrollment(): BelongsTo {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo {
        return $this->belongsTo(Lesson::class);
    }
}
