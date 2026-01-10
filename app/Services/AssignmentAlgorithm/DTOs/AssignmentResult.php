<?php

namespace App\Services\AssignmentAlgorithm\DTOs;

use Illuminate\Support\Collection;

/**
 * Data Transfer Object for assignment algorithm results
 */
class AssignmentResult
{
    public function __construct(
        public Collection $groups,        // Groups with assignedStudents collection
        public array $assignments,        // Array of ['student_id' => X, 'group_id' => Y]
        public array $unassigned,         // Array of Student models
        public array $warnings            // Array of warning arrays
    ) {}

    /**
     * Get count of successfully assigned students
     */
    public function getAssignedCount(): int
    {
        return count($this->assignments);
    }

    /**
     * Get count of unassigned students
     */
    public function getUnassignedCount(): int
    {
        return count($this->unassigned);
    }

    /**
     * Check if there are any warnings
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * Get warnings by severity
     */
    public function getWarningsBySeverity(string $severity): array
    {
        return array_filter($this->warnings, fn($w) => $w['severity'] === $severity);
    }

    /**
     * Get errors (severity = 'error')
     */
    public function getErrors(): array
    {
        return $this->getWarningsBySeverity('error');
    }
}
