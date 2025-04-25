<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    public function section(): BelongsTo {
        return $this->belongsTo(Section::class);
    }

    public function lessonProgress(): HasMany {
        return $this->hasMany(LessonProgress::class);
    }
}

