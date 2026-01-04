<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ["name", "classroom_id"];

    public function groupPreferences(): HasMany
    {
        return $this->hasMany(GroupPreferences::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'groups_students')
                    ->withPivot('assignment_method', 'assigned_at', 'assigned_by')
                    ->withTimestamps();
    }
}
