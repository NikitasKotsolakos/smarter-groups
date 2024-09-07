<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Classroom
 *
 * @mixin Eloquent
 */
class Classroom extends Model
{
    protected $fillable = ["name", "grade"];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

}
