<?php

namespace App\Services\AssignmentAlgorithm;

use App\Models\Student;
use App\Models\Group;
use Illuminate\Support\Collection;

/**
 * Validates constraints for student-to-group assignments
 * Equivalent to constraint checking in Java Main.java lines 79-80
 */
class ConstraintChecker
{
    /**
     * Check if student can be assigned to group
     * Implements constraint checking from Java (Main.java lines 79-80)
     *
     * @param Student $student
     * @param Group $group
     * @return bool True if can assign
     */
    public function canAssignStudentToGroup(Student $student, Group $group): bool
    {
        // Get current assigned students (in-memory collection)
        $assignedStudents = $group->assignedStudents ?? collect();

        // Constraint 1: Maximum capacity check
        if ($assignedStudents->count() >= $group->maximumParticipants) {
            return false;
        }

        // Constraint 2: Classroom mixing check
        $maxFromClassroom = $this->getMaxStudentsFromOneClassroom($group);
        $classroomCount = $assignedStudents->where('classroom_id', $student->classroom_id)->count();

        if ($classroomCount >= $maxFromClassroom) {
            return false;
        }

        return true;
    }

    /**
     * Get max students from one classroom for this group
     * If NULL/empty, use maximumParticipants as limit (no mixing constraint)
     */
    private function getMaxStudentsFromOneClassroom(Group $group): int
    {
        return $group->max_students_from_one_classroom ?? $group->maximumParticipants;
    }

    /**
     * Validate results and generate warnings
     * Equivalent to Java's validation (Main.java lines 92-102)
     *
     * @param Collection $groups Groups with assigned students
     * @param array $unassigned Unassigned students
     * @return array Warnings array
     */
    public function validateResults(Collection $groups, array $unassigned): array
    {
        $warnings = [];

        // Check groups below minimum capacity
        foreach ($groups as $group) {
            $count = isset($group->assignedStudents) ? $group->assignedStudents->count() : 0;

            if ($count < $group->minimumParticipants) {
                $warnings[] = [
                    'type' => 'under_minimum',
                    'severity' => 'warning',
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'current_count' => $count,
                    'minimum_required' => $group->minimumParticipants,
                    'message' => "Group '{$group->name}' has only {$count} students (minimum: {$group->minimumParticipants})"
                ];
            }
        }

        // Check unassigned students
        foreach ($unassigned as $student) {
            $warnings[] = [
                'type' => 'unassigned',
                'severity' => 'error',
                'student_id' => $student->id,
                'student_name' => $student->name,
                'classroom_name' => $student->classroom->name,
                'message' => "Student '{$student->name}' ({$student->classroom->name}) could not be assigned to any group"
            ];
        }

        return $warnings;
    }
}
