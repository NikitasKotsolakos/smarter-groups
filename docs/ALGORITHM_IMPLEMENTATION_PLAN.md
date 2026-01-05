# Assignment Algorithm - Laravel Implementation Plan

> **Project**: Group Splitter Laravel Application
> **Based On**: Java implementation at `/home/nikitas/programming/java/project-group-splitter-java`
> **Created**: 2026-01-05
> **Status**: Planning Phase

## Table of Contents
1. [Implementation Overview](#implementation-overview)
2. [Architecture Decisions](#architecture-decisions)
3. [Database Changes](#database-changes)
4. [Service Layer Implementation](#service-layer-implementation)
5. [Model Enhancements](#model-enhancements)
6. [Controller Integration](#controller-integration)
7. [Implementation Order](#implementation-order)
8. [Testing Strategy](#testing-strategy)
9. [Future Enhancements](#future-enhancements)

---

## Implementation Overview

### Goal
Port the Java assignment algorithm to Laravel, replacing the current round-robin stub with the full priority-based greedy assignment algorithm with dynamic priority adjustment.

### Key Principles
- **Database-driven configuration**: All settings stored in database (user-editable in future)
- **Service-based architecture**: Separate concerns into testable services
- **Non-destructive computation**: Algorithm doesn't modify database until final save
- **Same logic as Java**: Maintain algorithm behavior, adapt to Laravel patterns

---

## Architecture Decisions

### 1. Service Structure ✓ APPROVED
Separate algorithm into focused services:
- `AssignmentService` - Main orchestrator (equivalent to Main.java)
- `GroupSorter` - Project sorting strategies
- `StudentSorter` - Student sorting with preference urgency
- `ConstraintChecker` - Validation and constraint checking

### 2. Configuration Management ✓ APPROVED
- **Database-driven**: Store all configuration in `groups` table
- **User input**: Will come from UI forms in future
- **No config files**: Avoid `config/assignment.php` for now

### 3. Classroom Mixing Field ✓ APPROVED
- **Field name**: `max_students_from_one_classroom`
- **Location**: `groups` table (new column)
- **Nullable**: Yes
- **Default behavior**: If NULL/empty, use `maximumParticipants` as limit

### 4. Sorting Strategy ✓ APPROVED
- **Start with**: Random tie-breaking (CompPrioRandom equivalent)
- **Architecture**: Make it easy to add popularity-based later
- **Implementation**: Use strategy pattern or simple flag

### 5. Dynamic Priority Adjustment ✓ APPROVED
- **Behavior**: Same as Java (when group reaches minimum, priority → PHP_INT_MAX - popularity)
- **Storage**: Computed in-memory, NOT saved to database
- **Effect**: Groups at minimum go to "end", but most popular still come "first" among them

---

## Database Changes

### Migration 1: Add classroom mixing constraint to groups

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_classroom_mixing_to_groups_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('max_students_from_one_classroom')
                  ->nullable()
                  ->after('maximumParticipants')
                  ->comment('Maximum students from same classroom. NULL = no limit (uses maximumParticipants)');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('max_students_from_one_classroom');
        });
    }
};
```

### Model Update: Group.php

**Add to `$fillable`:**
```php
protected $fillable = [
    "name",
    "minimumParticipants",
    "maximumParticipants",
    "priorityGroup",
    "workshop_id",
    "max_students_from_one_classroom"  // NEW
];
```

---

## Service Layer Implementation

### Directory Structure

```
app/
└── Services/
    └── AssignmentAlgorithm/
        ├── AssignmentService.php
        ├── GroupSorter.php
        ├── StudentSorter.php
        ├── ConstraintChecker.php
        └── DTOs/
            ├── AssignmentResult.php
            └── GroupAssignment.php
```

---

### Service 1: AssignmentService.php

**Location**: `app/Services/AssignmentAlgorithm/AssignmentService.php`

**Responsibilities**:
- Orchestrate the entire assignment process
- Equivalent to `Main.main()` in Java

**Dependencies**:
- `GroupSorter` - for sorting groups by priority
- `StudentSorter` - for sorting students by preference urgency
- `ConstraintChecker` - for validating assignments

**Key Methods**:

```php
<?php

namespace App\Services\AssignmentAlgorithm;

use App\Models\Workshop;
use App\Services\AssignmentAlgorithm\DTOs\AssignmentResult;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    public function __construct(
        private GroupSorter $groupSorter,
        private StudentSorter $studentSorter,
        private ConstraintChecker $constraintChecker
    ) {}

    /**
     * Main algorithm entry point
     *
     * @param Workshop $workshop The workshop to assign
     * @return AssignmentResult Result with assignments and warnings
     */
    public function assignStudentsToGroups(Workshop $workshop): AssignmentResult
    {
        // PHASE 1: Load data with eager loading
        $groups = $this->loadGroups($workshop);
        $students = $this->loadStudents($workshop);

        Log::info("Algorithm started", [
            'workshop_id' => $workshop->id,
            'groups_count' => $groups->count(),
            'students_count' => $students->count()
        ]);

        // PHASE 2: Sort groups and reorder student preferences
        $sortedGroups = $this->groupSorter->sortByPriority($groups);
        $groupOrderMap = $this->createGroupOrderMap($sortedGroups);
        $this->reorderStudentPreferences($students, $groupOrderMap);

        // PHASE 3: Sort students by preference urgency
        $sortedStudents = $this->studentSorter->sortByPreferenceUrgency($students);

        // PHASE 4: Assignment loop with constraint checking
        $assignments = $this->performAssignments($sortedStudents, $groups);

        // PHASE 5: Validation and warnings
        $warnings = $this->constraintChecker->validateResults($groups, $assignments['unassigned']);

        return new AssignmentResult(
            groups: $groups,
            assignments: $assignments['assigned'],
            unassigned: $assignments['unassigned'],
            warnings: $warnings
        );
    }

    /**
     * Load groups with popularity calculation
     */
    private function loadGroups(Workshop $workshop)
    {
        $groups = $workshop->groups()->get();

        // Calculate popularity for each group
        foreach ($groups as $group) {
            $group->popularity = $group->groupPreferences()->count();
        }

        return $groups;
    }

    /**
     * Load students with preferences and classroom info
     */
    private function loadStudents(Workshop $workshop)
    {
        return $workshop->students()
            ->with([
                'groupPreferences.group',
                'classroom'
            ])
            ->get();
    }

    /**
     * Create order map: group_id => position in sorted list
     * Equivalent to Java's projectsOrder map (Main.java lines 55-58)
     */
    private function createGroupOrderMap($sortedGroups): array
    {
        $map = [];
        foreach ($sortedGroups as $index => $group) {
            $map[$group->id] = $index;
        }
        return $map;
    }

    /**
     * Reorder each student's preferences based on global group priority order
     * Equivalent to Java's Student.sortPreferences() (Student.java lines 73-78)
     */
    private function reorderStudentPreferences($students, array $groupOrderMap): void
    {
        foreach ($students as $student) {
            // Get student's preferences ordered by rank
            $preferences = $student->groupPreferences()
                ->orderBy('rank')
                ->get();

            // Sort preferences by group priority order
            $sortedPreferences = $preferences->sortBy(function ($pref) use ($groupOrderMap) {
                return $groupOrderMap[$pref->group_id] ?? PHP_INT_MAX;
            });

            // Store sorted group IDs and their priorities on student object (for StudentSorter)
            $student->sortedGroupIds = $sortedPreferences->pluck('group_id')->toArray();
            $student->sortedPreferencePriorities = $sortedPreferences->map(function ($pref) use ($groupOrderMap) {
                return $groupOrderMap[$pref->group_id];
            })->toArray();
        }
    }

    /**
     * Perform student-to-group assignments
     * Equivalent to Java's assignment loop (Main.java lines 74-90)
     */
    private function performAssignments($sortedStudents, $groups): array
    {
        $assigned = [];
        $unassigned = [];

        // Create lookup map for groups by ID
        $groupsById = $groups->keyBy('id');

        foreach ($sortedStudents as $student) {
            $wasAssigned = false;

            // Try each preference in priority order
            foreach ($student->sortedGroupIds as $groupId) {
                $group = $groupsById[$groupId];

                // Check constraints
                if ($this->constraintChecker->canAssignStudentToGroup($student, $group)) {
                    // Assign student to group (in memory)
                    $this->assignStudentToGroup($student, $group);
                    $assigned[] = ['student_id' => $student->id, 'group_id' => $group->id];
                    $wasAssigned = true;

                    // Update group's dynamic priority if reached minimum
                    $this->updateGroupDynamicPriority($group);

                    break; // Move to next student
                }
            }

            if (!$wasAssigned) {
                $unassigned[] = $student;
            }
        }

        return [
            'assigned' => $assigned,
            'unassigned' => $unassigned
        ];
    }

    /**
     * Assign student to group (in-memory collection, not database)
     */
    private function assignStudentToGroup($student, $group): void
    {
        // Initialize collection if doesn't exist
        if (!isset($group->assignedStudents)) {
            $group->assignedStudents = collect();
        }

        $group->assignedStudents->push($student);
    }

    /**
     * Update group's dynamic priority when reaching minimum
     * Equivalent to Java's Project.addParticipant() priority logic (Project.java line 157)
     */
    private function updateGroupDynamicPriority($group): void
    {
        $currentCount = $group->assignedStudents->count();

        if ($currentCount >= $group->minimumParticipants) {
            // Set priority to PHP_INT_MAX - popularity
            // This pushes it to "end" but most popular still come "first"
            $group->dynamicPriority = PHP_INT_MAX - $group->popularity;
        }
    }
}
```

**Key Implementation Notes**:
1. Uses in-memory collections (`$group->assignedStudents`) to track assignments
2. Does NOT modify database during algorithm execution
3. Returns `AssignmentResult` DTO with all data needed for saving
4. Logs important steps for debugging

---

### Service 2: GroupSorter.php

**Location**: `app/Services/AssignmentAlgorithm/GroupSorter.php`

**Responsibilities**:
- Sort groups by priority
- Implement sorting strategies (random, popularity-based)

**Key Methods**:

```php
<?php

namespace App\Services\AssignmentAlgorithm;

use Illuminate\Support\Collection;

class GroupSorter
{
    /**
     * Sort groups by priority with random tie-breaking
     * Equivalent to Java's CompPrioRandom (CompPrioRandom.java)
     *
     * @param Collection $groups
     * @return Collection Sorted groups
     */
    public function sortByPriority(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            // Primary sort: priorityGroup (ascending - lower number = higher priority)
            // Secondary sort: random (for tie-breaking)
            return [$group->priorityGroup, rand()];
        })->values();
    }

    /**
     * Alternative: Sort by priority with popularity tie-breaking
     * Equivalent to Java's CompPrioPopul (CompPrioPopul.java)
     * FUTURE ENHANCEMENT - not implemented yet
     *
     * @param Collection $groups
     * @return Collection Sorted groups
     */
    public function sortByPriorityAndPopularity(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            // Primary sort: priorityGroup (ascending)
            // Secondary sort: popularity (ascending - less popular first)
            return [$group->priorityGroup, $group->popularity];
        })->values();
    }
}
```

**Architecture Note**:
- Easy to switch between sorting strategies
- Can add configuration flag later: `$usePriorityTieBreaking = false`
- For MVP: use random tie-breaking only

---

### Service 3: StudentSorter.php

**Location**: `app/Services/AssignmentAlgorithm/StudentSorter.php`

**Responsibilities**:
- Sort students by preference urgency
- Equivalent to Java's StudentComparator

**Key Methods**:

```php
<?php

namespace App\Services\AssignmentAlgorithm;

use Illuminate\Support\Collection;

class StudentSorter
{
    /**
     * Sort students by preference urgency
     * Equivalent to Java's StudentComparator (StudentComparator.java)
     *
     * Algorithm:
     * 1. Shuffle students first (randomize base order)
     * 2. Sort by comparing preference priorities position-by-position
     * 3. If all compared priorities equal, fewer preferences comes first
     * 4. If everything equal, maintain random order from shuffle
     *
     * @param Collection $students Students with sortedPreferencePriorities set
     * @return Collection Sorted students
     */
    public function sortByPreferenceUrgency(Collection $students): Collection
    {
        // Step 1: Shuffle (equivalent to Java's Collections.shuffle - Main.java line 66)
        $shuffled = $students->shuffle();

        // Step 2: Sort by preference urgency (equivalent to Main.java line 69)
        return $shuffled->sort(function ($studentA, $studentB) {
            $prioritiesA = $studentA->sortedPreferencePriorities ?? [];
            $prioritiesB = $studentB->sortedPreferencePriorities ?? [];

            // Compare priorities position by position
            $minLength = min(count($prioritiesA), count($prioritiesB));

            for ($i = 0; $i < $minLength; $i++) {
                $comparison = $prioritiesA[$i] <=> $prioritiesB[$i];
                if ($comparison !== 0) {
                    return $comparison; // Return first difference found
                }
            }

            // If all compared priorities are equal, sort by number of preferences
            // Fewer preferences = more constrained = should go first
            $lengthComparison = count($prioritiesA) <=> count($prioritiesB);

            if ($lengthComparison !== 0) {
                return $lengthComparison;
            }

            // If everything is equal, maintain random order from shuffle
            // (PHP's sort is stable, so shuffle order is preserved)
            return 0;
        })->values(); // Re-index array
    }
}
```

**Example Behavior**:
- Student A: priorities `[0, 2, 10]` (Band, Sport, Häkeln)
- Student B: priorities `[0, 3, 4]` (Band, zeichnen lernen, Schach)
- **Result**: A comes first (both have 0, but A's second is 2 < B's 3)

---

### Service 4: ConstraintChecker.php

**Location**: `app/Services/AssignmentAlgorithm/ConstraintChecker.php`

**Responsibilities**:
- Check if student can be assigned to group
- Validate final results
- Generate warnings

**Key Methods**:

```php
<?php

namespace App\Services\AssignmentAlgorithm;

use App\Models\Student;
use App\Models\Group;
use Illuminate\Support\Collection;

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
            $count = $group->assignedStudents->count() ?? 0;

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
```

---

### DTO: AssignmentResult.php

**Location**: `app/Services/AssignmentAlgorithm/DTOs/AssignmentResult.php`

**Responsibilities**:
- Encapsulate algorithm results
- Provide convenient access to assignment data

```php
<?php

namespace App\Services\AssignmentAlgorithm\DTOs;

use Illuminate\Support\Collection;

class AssignmentResult
{
    public function __construct(
        public Collection $groups,        // Groups with assignedStudents collection
        public array $assignments,        // Array of ['student_id' => X, 'group_id' => Y]
        public array $unassigned,        // Array of Student models
        public array $warnings           // Array of warning arrays
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
```

---

## Model Enhancements

### Group Model Updates

**Location**: `app/Models/Group.php`

**New Methods**:

```php
/**
 * Get popularity (how many students selected this group)
 * Equivalent to Java's Project.popularity field
 */
public function getPopularity(): int
{
    return $this->groupPreferences()->count();
}

/**
 * Get effective max students from one classroom
 * Returns max_students_from_one_classroom if set, else maximumParticipants
 */
public function getEffectiveMaxFromClassroom(): int
{
    return $this->max_students_from_one_classroom ?? $this->maximumParticipants;
}
```

**Updated Fillable**:
```php
protected $fillable = [
    "name",
    "minimumParticipants",
    "maximumParticipants",
    "priorityGroup",
    "workshop_id",
    "max_students_from_one_classroom"  // NEW
];
```

### Student Model Updates

**Location**: `app/Models/Student.php`

**No changes needed** - algorithm stores temporary data as object properties (`sortedGroupIds`, `sortedPreferencePriorities`) during execution.

---

## Controller Integration

### Update WorkshopController

**Location**: `app/Http/Controllers/WorkshopController.php`

**Method**: `runAssignmentAlgorithm()`

**Changes**:

```php
use App\Services\AssignmentAlgorithm\AssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function runAssignmentAlgorithm(Request $request, Workshop $workshop)
{
    // Validate preconditions
    if ($workshop->groups()->count() === 0) {
        return redirect(route('workshops.show', $workshop->id) . '#assignments')
            ->withErrors(['error' => 'Cannot run algorithm: No groups defined']);
    }

    if ($workshop->students()->count() === 0) {
        return redirect(route('workshops.show', $workshop->id) . '#assignments')
            ->withErrors(['error' => 'Cannot run algorithm: No students defined']);
    }

    try {
        return DB::transaction(function () use ($workshop) {
            // Clear existing assignments
            DB::table('groups_students')
                ->whereIn('group_id', $workshop->groups()->pluck('id'))
                ->delete();

            // Run the assignment algorithm
            $assignmentService = app(AssignmentService::class);
            $result = $assignmentService->assignStudentsToGroups($workshop);

            // Save assignments to database
            foreach ($result->assignments as $assignment) {
                DB::table('groups_students')->insert([
                    'group_id' => $assignment['group_id'],
                    'student_id' => $assignment['student_id'],
                    'assignment_method' => 'algorithm',
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update workshop status
            $workshop->update(['assignment_status' => 'generated']);

            // Prepare success message
            $message = sprintf(
                'Algorithm completed! %d students assigned to %d groups.',
                $result->getAssignedCount(),
                $workshop->groups()->count()
            );

            if ($result->hasWarnings()) {
                $message .= ' Check warnings below.';
            }

            Log::info('Assignment algorithm completed', [
                'workshop_id' => $workshop->id,
                'assigned_count' => $result->getAssignedCount(),
                'unassigned_count' => $result->getUnassignedCount(),
                'warnings_count' => count($result->warnings)
            ]);

            return redirect(route('workshops.show', $workshop->id) . '#assignments')
                ->with('success', $message)
                ->with('assignment_warnings', $result->warnings);
        });
    } catch (\Exception $e) {
        Log::error('Assignment algorithm failed', [
            'workshop_id' => $workshop->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect(route('workshops.show', $workshop->id) . '#assignments')
            ->withErrors(['error' => 'Algorithm failed: ' . $e->getMessage()]);
    }
}
```

### Display Warnings in View

**Location**: `resources/views/workshops/partials/assignments-display.blade.php`

**Add after action bar**:

```blade
{{-- Algorithm Warnings --}}
@if(session('assignment_warnings'))
    <div class="mb-6 space-y-2">
        @foreach(session('assignment_warnings') as $warning)
            <div class="p-4 rounded-md
                @if($warning['severity'] === 'error') bg-red-50 border border-red-200
                @else bg-yellow-50 border border-yellow-200
                @endif">
                <p class="text-sm
                    @if($warning['severity'] === 'error') text-red-800
                    @else text-yellow-800
                    @endif">
                    @if($warning['severity'] === 'error') ❌ @else ⚠ @endif
                    {{ $warning['message'] }}
                </p>
            </div>
        @endforeach
    </div>
@endif
```

---

## Implementation Order

### Phase 1: Database & Models (30 min)
1. ✅ Create migration for `max_students_from_one_classroom`
2. ✅ Run migration
3. ✅ Update `Group` model fillable
4. ✅ Add `getPopularity()` and `getEffectiveMaxFromClassroom()` to Group model

### Phase 2: Service Layer (2-3 hours)
5. ✅ Create directory `app/Services/AssignmentAlgorithm/`
6. ✅ Create `AssignmentResult` DTO
7. ✅ Implement `ConstraintChecker` service (simplest, needed by others)
8. ✅ Implement `GroupSorter` service
9. ✅ Implement `StudentSorter` service
10. ✅ Implement `AssignmentService` (main orchestrator)

### Phase 3: Controller Integration (30 min)
11. ✅ Update `WorkshopController::runAssignmentAlgorithm()`
12. ✅ Add error handling and logging

### Phase 4: View Updates (15 min)
13. ✅ Add warnings display to `assignments-display.blade.php`
14. ✅ Test warning styling

### Phase 5: Testing (1-2 hours)
15. ✅ Test with seed data (Workshop 2)
16. ✅ Verify assignments match expected behavior
17. ✅ Test edge cases (no preferences, over capacity, etc.)
18. ✅ Verify warnings display correctly

### Phase 6: Documentation (30 min)
19. ✅ Update `Claude.md` with algorithm details
20. ✅ Update `IMPLEMENTATION_PLAN.md` status
21. ✅ Add comments to complex algorithm sections

**Total Estimated Time**: 4-6 hours

---

## Testing Strategy

### Unit Tests (Future)

**Location**: `tests/Unit/Services/AssignmentAlgorithm/`

```
GroupSorterTest.php
├── test_sorts_by_priority_ascending()
├── test_random_tie_breaking()
└── test_handles_empty_collection()

StudentSorterTest.php
├── test_sorts_by_preference_urgency()
├── test_fewer_preferences_comes_first()
├── test_shuffle_before_sort()
└── test_handles_students_without_preferences()

ConstraintCheckerTest.php
├── test_respects_maximum_capacity()
├── test_respects_classroom_mixing()
├── test_uses_max_participants_when_mixing_null()
├── test_validates_minimum_capacity()
└── test_identifies_unassigned_students()
```

### Integration Tests (Future)

**Location**: `tests/Feature/AssignmentAlgorithm/`

```
AssignmentServiceTest.php
├── test_assigns_all_students_basic_case()
├── test_respects_group_priorities()
├── test_respects_capacity_constraints()
├── test_respects_classroom_mixing()
├── test_handles_dynamic_priority_adjustment()
├── test_handles_insufficient_capacity()
├── test_generates_warnings_for_under_minimum()
└── test_generates_errors_for_unassigned()
```

### Manual Testing Scenarios

1. **Basic Assignment**:
   - All students have preferences
   - All groups have capacity
   - All students should be assigned

2. **Capacity Overflow**:
   - More students want popular group than max capacity
   - Students should fallback to 2nd/3rd choices

3. **Classroom Mixing**:
   - Set `max_students_from_one_classroom = 2`
   - Verify no group has >2 from same classroom

4. **Priority Ordering**:
   - Set Band priority=1, Sport priority=10
   - Verify Band fills first

5. **Dynamic Priority**:
   - Group with min=8, max=12, priority=1
   - Verify when reaches 8, other groups start filling

6. **Unassigned Students**:
   - Student with only 1 preference to full group
   - Should appear in unassigned with error

7. **Under Minimum**:
   - Group with min=10, only 5 students want it
   - Should show warning

---

## Future Enhancements

### Immediate Next Steps (Post-MVP)
1. **Popularity-based tie-breaking**: Add flag to switch between random and popularity
2. **Configuration UI**: Allow teachers to set `max_students_from_one_classroom` in group edit form
3. **Better error messages**: More specific constraint violation messages
4. **Assignment metrics**: Calculate preference satisfaction rate

### Medium-term Enhancements
1. **Weighted preferences**: Allow students to weight their preferences (1st = 3 points, 2nd = 2, 3rd = 1)
2. **Manual refinement**: Allow swapping students between groups after algorithm
3. **Multiple algorithm runs**: Save and compare different runs
4. **Optimization scoring**: Calculate and display "quality score" for assignments

### Long-term Enhancements
1. **Linear programming solver**: Replace greedy with optimal solver (CPLEX, Gurobi)
2. **Constraint programming**: Use CP-SAT solver for complex constraints
3. **Machine learning**: Predict student satisfaction based on past data
4. **A/B testing**: Compare different algorithm variants

---

## Risk Mitigation

### Potential Issues

1. **Performance with large datasets**:
   - **Risk**: Slow with 1000+ students
   - **Mitigation**: Profile with large test data, optimize N+1 queries
   - **Threshold**: Target <5 seconds for 1000 students

2. **Unassigned students**:
   - **Risk**: Many students can't be assigned
   - **Mitigation**: Clear error messages, suggest manual assignment
   - **Fallback**: UI to manually assign unassigned students

3. **Under-minimum groups**:
   - **Risk**: Unpopular groups don't reach minimum
   - **Mitigation**: Warnings displayed, allow proceeding anyway
   - **Future**: Auto-merge small groups or suggest consolidation

4. **Algorithm differences from Java**:
   - **Risk**: Subtle behavior differences cause unexpected results
   - **Mitigation**: Comprehensive testing, compare results with Java on same data
   - **Validation**: Run both algorithms on sample CSV, compare outputs

5. **Concurrent modifications**:
   - **Risk**: User edits while algorithm running
   - **Mitigation**: Database transaction wraps everything
   - **UI**: Consider loading indicator, disable edits during run

---

## Configuration Reference

### Group Fields Used by Algorithm

| Field | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| `name` | string | Yes | - | Group name |
| `minimumParticipants` | integer | Yes | - | Minimum required students |
| `maximumParticipants` | integer | Yes | - | Maximum allowed students |
| `priorityGroup` | integer | Yes | 10 | Priority (lower = filled first) |
| `max_students_from_one_classroom` | integer | No | NULL | Max from one classroom (NULL = no limit) |

### Algorithm Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `PHP_INT_MAX` | 9223372036854775807 | Used for dynamic priority adjustment |
| Default shuffle | Enabled | Students shuffled before sorting |
| Student sorting | Enabled | Sort by preference urgency |
| Group sorting | Random tie-break | Use `CompPrioRandom` equivalent |

---

## Appendix: Code Mapping

### Java → Laravel Equivalents

| Java File | Laravel File | Notes |
|-----------|--------------|-------|
| `Main.java` | `AssignmentService.php` | Main orchestrator |
| `Project.java` | `Group` model + methods | Data + logic |
| `Student.java` | `Student` model + temp properties | Data + runtime state |
| `CompPrioRandom.java` | `GroupSorter::sortByPriority()` | Random tie-breaking |
| `CompPrioPopul.java` | `GroupSorter::sortByPriorityAndPopularity()` | Future enhancement |
| `StudentComparator.java` | `StudentSorter::sortByPreferenceUrgency()` | Student sorting |
| N/A | `ConstraintChecker` | Extracted for clarity |

### Key Algorithm Steps Mapping

| Java Code | Laravel Code | Line Reference |
|-----------|--------------|----------------|
| `initializeProjects()` | `loadGroups()` | Main.java:121 → AssignmentService.php |
| `initializeStudents()` | `loadStudents()` | Main.java:127 → AssignmentService.php |
| `sortedProjects.sort()` | `groupSorter->sortByPriority()` | Main.java:53 → AssignmentService.php |
| `projectsOrder map` | `createGroupOrderMap()` | Main.java:55-58 → AssignmentService.php |
| `student.sortPreferences()` | `reorderStudentPreferences()` | Student.java:73 → AssignmentService.php |
| `Collections.shuffle()` | `->shuffle()` | Main.java:66 → StudentSorter.php |
| `sortedStudents.sort()` | `sortByPreferenceUrgency()` | Main.java:69 → StudentSorter.php |
| Assignment loop | `performAssignments()` | Main.java:74-90 → AssignmentService.php |
| Validation | `validateResults()` | Main.java:92-102 → ConstraintChecker.php |

---

## Sign-off

**Prepared by**: Claude Code Assistant
**Reviewed by**: [To be filled]
**Approved by**: [To be filled]
**Date**: 2026-01-05

---

## Change Log

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-01-05 | 1.0 | Initial planning document | Claude |
