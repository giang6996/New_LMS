<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public function instructor(): BelongsTo {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany {
        return $this->hasMany(Section::class);
    }

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews(): HasMany {
        return $this->hasMany(Review::class);
    }
}
