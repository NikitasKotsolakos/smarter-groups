<?php

namespace App\Services\AssignmentAlgorithm;

use App\Models\Group;
use App\Models\Student;
use App\Models\Workshop;
use App\Services\AssignmentAlgorithm\DTOs\AssignmentResult;
use Illuminate\Support\Collection;

/**
 * Main assignment algorithm orchestrator
 * Equivalent to Java's Main.java
 *
 * Implements priority-based greedy algorithm with dynamic priority adjustment
 */
class AssignmentService
{
    private GroupSorter $groupSorter;

    private StudentSorter $studentSorter;

    private ConstraintChecker $constraintChecker;

    public function __construct()
    {
        $this->groupSorter = new GroupSorter;
        $this->studentSorter = new StudentSorter;
        $this->constraintChecker = new ConstraintChecker;
    }

    /**
     * Execute the assignment algorithm for a workshop
     * Equivalent to Java's Main.main() method
     */
    public function assignStudentsToGroups(Workshop $workshop): AssignmentResult
    {
        // Phase 1: Load and initialize data (Main.java lines 45-63)
        $groups = $this->loadAndInitializeGroups($workshop);
        $students = $this->loadStudents($workshop);

        // Phase 2: Sort groups by priority (Main.java line 66)
        $sortedGroups = $this->groupSorter->sortByPriority($groups);

        // Phase 3: Reorder student preferences based on group priority order (Main.java line 69)
        $this->reorderStudentPreferences($students, $sortedGroups);

        // Phase 4: Sort students by preference urgency (Main.java line 72)
        $sortedStudents = $this->studentSorter->sortByPreferenceUrgency($students);

        // Phase 5: Execute assignment loop with dynamic priority adjustment (Main.java lines 75-90)
        $unassigned = $this->executeAssignmentLoop($sortedStudents, $sortedGroups);

        // Phase 6: Prepare results and validate (Main.java lines 92-102)
        $assignments = $this->extractAssignments($sortedGroups);
        $warnings = $this->constraintChecker->validateResults($sortedGroups, $unassigned);

        return new AssignmentResult(
            groups: $sortedGroups,
            assignments: $assignments,
            unassigned: $unassigned,
            warnings: $warnings
        );
    }

    /**
     * Load groups and initialize with popularity and assigned students collection
     * Equivalent to Java's Main.java lines 45-58
     */
    private function loadAndInitializeGroups(Workshop $workshop): Collection
    {
        $groups = Group::where('workshop_id', $workshop->id)
            ->orderBy('priorityGroup')
            ->get();

        // Initialize each group with popularity and empty assignedStudents collection
        foreach ($groups as $group) {
            $group->popularity = $group->getPopularity();
            $group->assignedStudents = collect(); // In-memory collection for algorithm
        }

        return $groups;
    }

    /**
     * Load students with their preferences
     * Equivalent to Java's Main.java lines 60-63
     */
    private function loadStudents(Workshop $workshop): Collection
    {
        return Student::whereHas('classroom', function ($query) use ($workshop) {
            $query->where('workshop_id', $workshop->id);
        })
            ->with(['groupPreferences' => function ($query) {
                $query->orderBy('rank')->with('group');
            }, 'classroom'])
            ->get();
    }

    /**
     * Reorder student preferences based on sorted group order
     * Equivalent to Java's Main.java line 69 (reorderPreferences call)
     *
     * Creates sortedPreferencePriorities array for each student
     * Maps their preferences to the sorted group order
     */
    private function reorderStudentPreferences(Collection $students, Collection $sortedGroups): void
    {
        // Create a map of group_id => position in sorted order
        $groupPositionMap = $sortedGroups->pluck('priorityGroup', 'id')->toArray();

        foreach ($students as $student) {
            $sortedPreferencePriorities = [];

            // For each preference, get the group's position in sorted order
            foreach ($student->groupPreferences as $preference) {
                $groupId = $preference->group_id;
                if (isset($groupPositionMap[$groupId])) {
                    $sortedPreferencePriorities[] = $groupPositionMap[$groupId];
                }
            }

            // Store the sorted priorities on the student for use in StudentSorter
            $student->sortedPreferencePriorities = $sortedPreferencePriorities;
        }
    }

    /**
     * Execute the main assignment loop with dynamic priority adjustment
     * Equivalent to Java's Main.java lines 75-90
     *
     * @return array Unassigned students
     */
    private function executeAssignmentLoop(Collection $students, Collection $groups): array
    {
        $unassigned = [];

        foreach ($students as $student) {
            $assigned = false;

            // Iterate preferences in the *current* group priority order, so that
            // groups deprioritized via adjustGroupPriority() are tried last.
            $orderedPreferences = $student->groupPreferences->sortBy(
                fn ($preference) => $groups->firstWhere('id', $preference->group_id)?->priorityGroup ?? PHP_INT_MAX
            );

            foreach ($orderedPreferences as $preference) {
                $group = $groups->firstWhere('id', $preference->group_id);

                if ($group && $this->constraintChecker->canAssignStudentToGroup($student, $group)) {
                    // Assign student to group (in-memory)
                    $group->assignedStudents->push($student);
                    $assigned = true;

                    // Dynamic priority adjustment when group reaches minimum (Main.java lines 86-88)
                    if ($group->assignedStudents->count() === $group->minimumParticipants) {
                        $this->adjustGroupPriority($group, $groups);
                    }

                    break; // Student assigned, move to next student
                }
            }

            // If student couldn't be assigned to any preference, track as unassigned
            if (! $assigned) {
                $unassigned[] = $student;
            }
        }

        return $unassigned;
    }

    /**
     * Adjust group priority when it reaches minimum capacity
     * Equivalent to Java's Main.java lines 86-88
     *
     * Sets priority to PHP_INT_MAX - popularity, then re-sorts groups
     * This pushes group to end but keeps popular ones relatively higher
     */
    private function adjustGroupPriority(Group $group, Collection &$groups): void
    {
        // Set new priority (lower popularity = higher adjusted priority number = further back)
        $group->priorityGroup = PHP_INT_MAX - $group->popularity;

        // Re-sort the entire collection to reflect the new priority
        $groups = $this->groupSorter->sortByPriority($groups);
    }

    /**
     * Extract assignments from groups into flat array
     * Converts in-memory assignments to database-ready format
     */
    private function extractAssignments(Collection $groups): array
    {
        $assignments = [];

        foreach ($groups as $group) {
            if (isset($group->assignedStudents)) {
                foreach ($group->assignedStudents as $student) {
                    $assignments[] = [
                        'student_id' => $student->id,
                        'group_id' => $group->id,
                    ];
                }
            }
        }

        return $assignments;
    }
}
