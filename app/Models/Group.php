<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ["name", "minimumParticipants", "maximumParticipants", "priorityGroup", "workshop_id", "max_students_from_one_classroom"];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'groups_students')
                    ->withPivot('assignment_method', 'assigned_at', 'assigned_by')
                    ->withTimestamps();
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get current count of assigned students
     */
    public function getCurrentCount(): int
    {
        return $this->students()->count();
    }

    /**
     * Get capacity status: 'ok' (green), 'under' (yellow), 'over' (red)
     */
    public function getCapacityStatus(): string
    {
        $count = $this->getCurrentCount();

        if ($count < $this->minimumParticipants) {
            return 'under';
        }

        if ($count > $this->maximumParticipants) {
            return 'over';
        }

        return 'ok';
    }

    /**
     * Check if group has capacity for more students
     */
    public function hasCapacityFor(int $additionalStudents = 1): bool
    {
        return ($this->getCurrentCount() + $additionalStudents) <= $this->maximumParticipants;
    }

    /**
     * Get popularity (how many students selected this group as a preference)
     * Equivalent to Java's Project.popularity field
     */
    public function getPopularity(): int
    {
        return \App\Models\GroupPreferences::where('group_id', $this->id)->count();
    }

    /**
     * Get effective max students from one classroom
     * Returns max_students_from_one_classroom if set, else maximumParticipants (no limit)
     */
    public function getEffectiveMaxFromClassroom(): int
    {
        return $this->max_students_from_one_classroom ?? $this->maximumParticipants;
    }

}
