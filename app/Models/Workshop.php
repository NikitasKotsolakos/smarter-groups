<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Workshop extends Model
{
    protected $fillable = ["name", "user_id", "assignment_status"];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, Classroom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if workshop has any assignments
     */
    public function hasAssignments(): bool
    {
        return $this->assignment_status !== 'none';
    }

    /**
     * Get count of students without group assignments
     */
    public function getUnassignedStudentsCount(): int
    {
        return $this->students()
            ->whereDoesntHave('groups')
            ->count();
    }

}
