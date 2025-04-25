<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgress(): HasMany {
        return $this->hasMany(LessonProgress::class);
    }

    public function sectionProgress(): HasMany {
        return $this->hasMany(SectionProgress::class);
    }
}
