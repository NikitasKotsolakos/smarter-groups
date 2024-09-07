<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Classroom
 *
 * @mixin Eloquent
 */
class Classroom extends Model
{
    protected $fillable = ["name", "grade"];

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class);
    }
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

}
