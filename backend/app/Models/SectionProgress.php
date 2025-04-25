<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionProgress extends Model
{
    public function enrollment(): BelongsTo {
        return $this->belongsTo(Enrollment::class);
    }

    public function section(): BelongsTo {
        return $this->belongsTo(Section::class);
    }
}
