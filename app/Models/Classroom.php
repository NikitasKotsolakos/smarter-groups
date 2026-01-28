<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Classroom
 *
 * @mixin Eloquent
 */
class Classroom extends Model
{
    protected $fillable = ["name", "workshop_id"];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

}
