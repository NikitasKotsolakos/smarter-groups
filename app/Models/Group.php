<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ["name", "minimumParticipants", "maximumParticipants", "priorityGroup", "workshop_id"];

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

}
