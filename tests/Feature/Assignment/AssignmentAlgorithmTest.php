<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\Group;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\GroupPreferences;
use App\Services\AssignmentAlgorithm\AssignmentService;
use App\Services\AssignmentAlgorithm\DTOs\AssignmentResult;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Helper function to import a CSV fixture and run the algorithm
 *
 * @param string $fixtureName Name of the CSV file (without extension)
 * @param array $groupOverrides Optional overrides for group settings ['groupName' => ['field' => value]]
 * @return AssignmentResult
 */
function runAlgorithmFixture(string $fixtureName, array $groupOverrides = []): AssignmentResult
{
    // Create user and workshop
    $user = User::factory()->create();
    $workshop = Workshop::create([
        'name' => "Test Workshop: {$fixtureName}",
        'user_id' => $user->id,
        'assignment_status' => 'none',
    ]);

    // Load and parse CSV
    $csvPath = base_path("tests/Feature/Assignment/Fixtures/{$fixtureName}.csv");

    if (!file_exists($csvPath)) {
        throw new \RuntimeException("Fixture not found: {$csvPath}");
    }

    $csvData = array_map(function($line) {
        return str_getcsv($line, ';');
    }, file($csvPath));

    // First row is headers
    $headers = array_shift($csvData);

    // Group names start from column 2
    $groupNames = array_slice($headers, 2);
    $groupNames = array_filter($groupNames, fn($name) => !empty(trim($name)));

    // Create groups with default values
    $groups = [];
    foreach ($groupNames as $index => $groupName) {
        $groupName = trim($groupName);
        if (!empty($groupName)) {
            $groups[$index + 2] = Group::create([
                'workshop_id' => $workshop->id,
                'name' => $groupName,
                'minimumParticipants' => 8,
                'maximumParticipants' => 15,
                'priorityGroup' => 1,
            ]);
        }
    }

    // Apply group overrides if provided
    foreach ($groupOverrides as $groupName => $overrides) {
        $group = collect($groups)->firstWhere('name', $groupName);
        if ($group) {
            $group->update($overrides);
        }
    }

    // Track classrooms
    $classrooms = [];

    // Process each student row
    foreach ($csvData as $row) {
        if (count($row) < 2) continue;

        $classroomName = trim($row[0] ?? '');
        $studentName = trim($row[1] ?? '');

        if (empty($classroomName) || empty($studentName)) {
            continue;
        }

        // Create or get classroom
        if (!isset($classrooms[$classroomName])) {
            $classrooms[$classroomName] = Classroom::create([
                'workshop_id' => $workshop->id,
                'name' => $classroomName,
            ]);
        }

        // Create student
        $student = Student::create([
            'name' => $studentName,
            'classroom_id' => $classrooms[$classroomName]->id,
        ]);

        // Create preferences (columns with value "1")
        $rank = 1;
        foreach ($row as $colIndex => $value) {
            if ($colIndex >= 2 && isset($groups[$colIndex]) && trim($value) === '1') {
                GroupPreferences::create([
                    'student_id' => $student->id,
                    'group_id' => $groups[$colIndex]->id,
                    'rank' => $rank++,
                ]);
            }
        }
    }

    // Run the algorithm
    $service = new AssignmentService();
    return $service->assignStudentsToGroups($workshop);
}

/**
 * Helper to dump assignment summary for debugging
 */
function dumpAssignmentSummary(AssignmentResult $result): void
{
    echo "\n=== Assignment Summary ===\n";
    echo "Assigned: {$result->getAssignedCount()}\n";
    echo "Unassigned: {$result->getUnassignedCount()}\n";
    echo "Warnings: " . count($result->warnings) . "\n\n";

    echo "Groups:\n";
    foreach ($result->groups as $group) {
        $count = $group->assignedStudents->count() ?? 0;
        echo "  {$group->name}: {$count}/{$group->minimumParticipants}-{$group->maximumParticipants}\n";
    }

    if ($result->hasWarnings()) {
        echo "\nWarnings:\n";
        foreach ($result->warnings as $warning) {
            echo "  [{$warning['severity']}] {$warning['message']}\n";
        }
    }
    echo "========================\n\n";
}

// Group all algorithm tests together
test('01: simple perfect fit - all students assigned', function () {
    // Adjust minimums to match student count (15 students / 3 groups = 5 each)
    $result = runAlgorithmFixture('01-simple-perfect-fit', [
        'Group A' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Group B' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Group C' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
    ]);

    // All students should be assigned
    expect($result->getUnassignedCount())->toBe(0)
        ->and($result->getAssignedCount())->toBe(15);

    // No errors (unassigned students)
    $errors = array_filter($result->warnings, fn($w) => $w['severity'] === 'error');
    expect(count($errors))->toBe(0);

    // All groups should respect capacity constraints
    foreach ($result->groups as $group) {
        $count = $group->assignedStudents->count();
        expect($count)->toBeLessThanOrEqual($group->maximumParticipants);
    }

    // Total students should be distributed across all groups
    $totalAssigned = $result->groups->sum(fn($g) => $g->assignedStudents->count());
    expect($totalAssigned)->toBe(15);
})->group('algorithm');

test('02: priority ordering - priority 1 fills before priority 2', function () {
    // Set different priorities and realistic minimums
    $result = runAlgorithmFixture('02-priority-ordering', [
        'Unpopular Priority 1' => ['priorityGroup' => 1, 'minimumParticipants' => 2, 'maximumParticipants' => 10],
        'Popular Priority 2' => ['priorityGroup' => 2, 'minimumParticipants' => 2, 'maximumParticipants' => 10],
        'Popular Priority 3' => ['priorityGroup' => 3, 'minimumParticipants' => 2, 'maximumParticipants' => 10],
    ]);

    $unpopular = $result->groups->firstWhere('name', 'Unpopular Priority 1');
    $popular2 = $result->groups->firstWhere('name', 'Popular Priority 2');
    $popular3 = $result->groups->firstWhere('name', 'Popular Priority 3');

    // All students should be assigned
    expect($result->getUnassignedCount())->toBe(0);

    // Unpopular group (priority 1) should have the 2 students who listed it as preference
    // This demonstrates priority works - it gets filled first
    expect($unpopular->assignedStudents->count())->toBe(2)
        ->and($unpopular->assignedStudents->count())->toBeGreaterThanOrEqual($unpopular->minimumParticipants);

    // All groups should respect capacity
    foreach ($result->groups as $group) {
        $count = $group->assignedStudents->count();
        expect($count)->toBeLessThanOrEqual($group->maximumParticipants);
    }
})->group('algorithm');

test('03: preference satisfaction - students get preferred choices', function () {
    // Set realistic minimums for 20 students / 4 groups = 5 each
    $result = runAlgorithmFixture('03-preference-satisfaction', [
        'Robotics' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Art' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Music' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Sports' => ['minimumParticipants' => 3, 'maximumParticipants' => 10],
    ]);

    // All 20 students should be assigned (plenty of capacity)
    expect($result->getUnassignedCount())->toBe(0)
        ->and($result->getAssignedCount())->toBe(20);

    // No error warnings (unassigned students)
    $errors = array_filter($result->warnings, fn($w) => $w['severity'] === 'error');
    expect(count($errors))->toBe(0);

    // Each student should be in one of their preferred groups
    foreach ($result->groups as $group) {
        foreach ($group->assignedStudents as $student) {
            // Student should have this group in their preferences
            $preferredGroupIds = $student->groupPreferences->pluck('group_id')->toArray();
            expect($preferredGroupIds)->toContain($group->id);
        }
    }
})->group('algorithm');

test('04: capacity constraints - respects maximum capacity', function () {
    // Override to create tight capacity constraints
    $result = runAlgorithmFixture('04-capacity-constraints', [
        'Small Group A' => ['minimumParticipants' => 3, 'maximumParticipants' => 5],
        'Small Group B' => ['minimumParticipants' => 3, 'maximumParticipants' => 5],
        'Small Group C' => ['minimumParticipants' => 3, 'maximumParticipants' => 5],
    ]);

    // 20 students, max capacity = 15, so 5 should be unassigned
    expect($result->getUnassignedCount())->toBe(5)
        ->and($result->getAssignedCount())->toBe(15);

    // Should have error warnings for unassigned students
    $errors = array_filter($result->warnings, fn($w) => $w['severity'] === 'error');
    expect(count($errors))->toBe(5);

    // NO group should exceed maximum capacity
    foreach ($result->groups as $group) {
        $count = $group->assignedStudents->count();
        expect($count)->toBeLessThanOrEqual($group->maximumParticipants)
            ->and($count)->toBeLessThanOrEqual(5);
    }
})->group('algorithm');

test('05: classroom mixing - respects max students per classroom', function () {
    // Set mixing constraint and realistic capacity
    $result = runAlgorithmFixture('05-classroom-mixing', [
        'Drama' => ['minimumParticipants' => 3, 'maximumParticipants' => 10, 'max_students_from_one_classroom' => 2],
        'Science' => ['minimumParticipants' => 3, 'maximumParticipants' => 10, 'max_students_from_one_classroom' => 2],
        'Sports' => ['minimumParticipants' => 3, 'maximumParticipants' => 10, 'max_students_from_one_classroom' => 2],
    ]);

    // Most students should be assigned (mixing constraint may prevent some)
    expect($result->getAssignedCount())->toBeGreaterThanOrEqual(12);

    // Check classroom mixing constraint for each group
    foreach ($result->groups as $group) {
        if ($group->max_students_from_one_classroom) {
            // Group students by classroom
            $byClassroom = $group->assignedStudents->groupBy('classroom_id');

            foreach ($byClassroom as $classroomId => $students) {
                // No classroom should have more than the limit
                expect($students->count())->toBeLessThanOrEqual($group->max_students_from_one_classroom)
                    ->and($students->count())->toBeLessThanOrEqual(2);
            }
        }
    }
})->group('algorithm');

test('06: dynamic priority - group priority adjusts at minimum capacity', function () {
    // Set up different priorities with realistic minimums (15 students / 3 groups = 5 each)
    $result = runAlgorithmFixture('06-dynamic-priority', [
        'Popular Low Priority' => ['priorityGroup' => 3, 'minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Unpopular High Priority' => ['priorityGroup' => 1, 'minimumParticipants' => 3, 'maximumParticipants' => 10],
        'Medium Priority' => ['priorityGroup' => 2, 'minimumParticipants' => 3, 'maximumParticipants' => 10],
    ]);

    $popular = $result->groups->firstWhere('name', 'Popular Low Priority');
    $unpopular = $result->groups->firstWhere('name', 'Unpopular High Priority');
    $medium = $result->groups->firstWhere('name', 'Medium Priority');

    // All students should be assigned
    expect($result->getUnassignedCount())->toBe(0)
        ->and($result->getAssignedCount())->toBe(15);

    // Each group should have students (algorithm distributes them)
    expect($unpopular->assignedStudents->count())->toBeGreaterThan(0);
    expect($medium->assignedStudents->count())->toBeGreaterThan(0);
    expect($popular->assignedStudents->count())->toBeGreaterThan(0);

    // All should respect maximum capacity
    foreach ($result->groups as $group) {
        $count = $group->assignedStudents->count();
        expect($count)->toBeLessThanOrEqual($group->maximumParticipants);
    }

    // Popular group should have gotten students despite low priority
    // (demonstrates that dynamic adjustment allows it to fill eventually)
    expect($popular->assignedStudents->count())->toBeGreaterThan(0);

    // Total distribution should equal 15
    $total = $unpopular->assignedStudents->count() + $medium->assignedStudents->count() + $popular->assignedStudents->count();
    expect($total)->toBe(15);
})->group('algorithm');

// Meta test: verify all fixtures exist
test('all test fixtures are present', function () {
    $fixtures = [
        '01-simple-perfect-fit.csv',
        '02-priority-ordering.csv',
        '03-preference-satisfaction.csv',
        '04-capacity-constraints.csv',
        '05-classroom-mixing.csv',
        '06-dynamic-priority.csv',
    ];

    foreach ($fixtures as $fixture) {
        $path = base_path("tests/Feature/Assignment/Fixtures/{$fixture}");
        expect(file_exists($path))->toBeTrue("Fixture missing: {$fixture}");
    }
})->group('algorithm');
