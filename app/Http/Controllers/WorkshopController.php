<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Group;
use App\Models\GroupPreferences;
use App\Models\Student;
use App\Models\Workshop;
use App\Services\AssignmentAlgorithm\AssignmentService;
use App\Exports\AssignmentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class WorkshopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $workshops = Workshop::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('workshops.index', ['workshops' => $workshops]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('workshops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workshop = Workshop::create([
            'name' => $request->input('name'),
            'user_id' => auth()->id(),
        ]);

        return redirect(route('workshops.show', $workshop->id))
            ->with('success', 'Workshop created successfully! Now add groups, classrooms, and students.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Workshop $workshop)
    {
        // Eager load relationships for assignments display
        $workshop->load([
            'groups.students.classroom',
            'students.groups'
        ]);

        // Get unassigned students
        $unassignedStudents = $workshop->students()
            ->whereDoesntHave('groups')
            ->with('classroom')
            ->get();

        return view('workshops.show', [
            'workshop' => $workshop,
            'unassignedStudents' => $unassignedStudents
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workshop $workshop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workshop $workshop)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'groupNames.*' => 'nullable|string|max:255',
            'minimumParticipants.*' => 'required|integer|min:0',
            'maximumParticipants.*' => 'required|integer|min:0',
            'priorityGroups.*' => 'required|integer|min:1',
            'newGroupNames.*' => 'nullable|string|max:255',
            'newMinimumParticipants.*' => 'nullable|integer|min:0',
            'newMaximumParticipants.*' => 'nullable|integer|min:0',
            'newPriorityGroups.*' => 'nullable|integer|min:1',
            'classroomNames.*' => 'nullable|string|max:255',
            'newClassroomNames.*' => 'nullable|string|max:255',
            'studentNames.*' => 'nullable|string|max:255',
            'studentClassrooms.*' => 'nullable|integer|exists:classrooms,id',
            'studentPreference1.*' => 'nullable|integer|exists:groups,id',
            'studentPreference2.*' => 'nullable|integer|exists:groups,id',
            'studentPreference3.*' => 'nullable|integer|exists:groups,id',
            'newStudentNames.*' => 'nullable|string|max:255',
            'newStudentClassrooms.*' => 'nullable|integer|exists:classrooms,id',
            'newStudentPreference1.*' => 'nullable|integer|exists:groups,id',
            'newStudentPreference2.*' => 'nullable|integer|exists:groups,id',
            'newStudentPreference3.*' => 'nullable|integer|exists:groups,id',
        ]);

        return DB::transaction(function () use ($request, $workshop) {
            // Update workshop name
            $workshop->update([
                'name' => $request->input('name'),
            ]);

            // Update existing groups
            $groupIds = $request->input('groupIds', []);
            $groupNames = $request->input('groupNames', []);
            $minimumParticipants = $request->input('minimumParticipants', []);
            $maximumParticipants = $request->input('maximumParticipants', []);
            $priorityGroups = $request->input('priorityGroups', []);

            foreach ($groupIds as $index => $groupId) {
                if (!empty($groupNames[$index])) {
                    $min = (int) $minimumParticipants[$index];
                    $max = (int) $maximumParticipants[$index];

                    // Validate min <= max
                    if ($min > $max) {
                        return redirect(route('workshops.show', $workshop->id))
                            ->withErrors(['error' => "Group '{$groupNames[$index]}': Minimum participants cannot be greater than maximum participants."])
                            ->withInput();
                    }

                    Group::where('id', $groupId)
                        ->where('workshop_id', $workshop->id) // Ensure group belongs to this workshop
                        ->update([
                            'name' => $groupNames[$index],
                            'minimumParticipants' => $min,
                            'maximumParticipants' => $max,
                            'priorityGroup' => $priorityGroups[$index],
                        ]);
                }
            }

            // Create new groups
            $newGroupNames = $request->input('newGroupNames', []);
            $newMinimumParticipants = $request->input('newMinimumParticipants', []);
            $newMaximumParticipants = $request->input('newMaximumParticipants', []);
            $newPriorityGroups = $request->input('newPriorityGroups', []);

            foreach ($newGroupNames as $index => $groupName) {
                if (!empty($groupName)) {
                    $min = (int) ($newMinimumParticipants[$index] ?? 10);
                    $max = (int) ($newMaximumParticipants[$index] ?? 20);
                    $priority = (int) ($newPriorityGroups[$index] ?? 1);

                    // Validate min <= max
                    if ($min > $max) {
                        return redirect(route('workshops.show', $workshop->id))
                            ->withErrors(['error' => "Group '{$groupName}': Minimum participants cannot be greater than maximum participants."])
                            ->withInput();
                    }

                    Group::create([
                        'workshop_id' => $workshop->id,
                        'name' => $groupName,
                        'minimumParticipants' => $min,
                        'maximumParticipants' => $max,
                        'priorityGroup' => $priority,
                    ]);
                }
            }

            // Update existing classrooms
            $classroomIds = $request->input('classroomIds', []);
            $classroomNames = $request->input('classroomNames', []);

            foreach ($classroomIds as $index => $classroomId) {
                if (!empty($classroomNames[$index])) {
                    Classroom::where('id', $classroomId)
                        ->where('workshop_id', $workshop->id) // Ensure classroom belongs to this workshop
                        ->update([
                            'name' => $classroomNames[$index],
                        ]);
                }
            }

            // Create new classrooms
            $newClassroomNames = $request->input('newClassroomNames', []);
            foreach ($newClassroomNames as $classroomName) {
                if (!empty($classroomName)) {
                    Classroom::create([
                        'name' => $classroomName,
                        'workshop_id' => $workshop->id,
                    ]);
                }
            }

            // Update existing students
            $studentIds = $request->input('studentIds', []);
            $studentNames = $request->input('studentNames', []);
            $studentClassrooms = $request->input('studentClassrooms', []);
            $studentPreference1 = $request->input('studentPreference1', []);
            $studentPreference2 = $request->input('studentPreference2', []);
            $studentPreference3 = $request->input('studentPreference3', []);

            foreach ($studentIds as $index => $studentId) {
                if (!empty($studentNames[$index])) {
                    // Get student and verify it belongs to this workshop (through classroom)
                    $student = Student::find($studentId);
                    if ($student && $student->classroom && $student->classroom->workshop_id == $workshop->id) {
                        // Update student
                        $student->update([
                            'name' => $studentNames[$index],
                            'classroom_id' => $studentClassrooms[$index] ?? null,
                        ]);

                        // Delete existing preferences and recreate
                        $student->groupPreferences()->delete();

                        // Create new preferences
                        $preferences = [
                            1 => $studentPreference1[$index] ?? null,
                            2 => $studentPreference2[$index] ?? null,
                            3 => $studentPreference3[$index] ?? null,
                        ];

                        foreach ($preferences as $rank => $groupId) {
                            if (!empty($groupId)) {
                                GroupPreferences::create([
                                    'student_id' => $student->id,
                                    'group_id' => $groupId,
                                    'rank' => $rank,
                                ]);
                            }
                        }
                    }
                }
            }

            // Create new students
            $newStudentNames = $request->input('newStudentNames', []);
            $newStudentClassrooms = $request->input('newStudentClassrooms', []);
            $newStudentPreference1 = $request->input('newStudentPreference1', []);
            $newStudentPreference2 = $request->input('newStudentPreference2', []);
            $newStudentPreference3 = $request->input('newStudentPreference3', []);

            foreach ($newStudentNames as $index => $studentName) {
                if (!empty($studentName) && !empty($newStudentClassrooms[$index])) {
                    // Create student
                    $student = Student::create([
                        'name' => $studentName,
                        'classroom_id' => $newStudentClassrooms[$index],
                    ]);

                    // Create preferences
                    $preferences = [
                        1 => $newStudentPreference1[$index] ?? null,
                        2 => $newStudentPreference2[$index] ?? null,
                        3 => $newStudentPreference3[$index] ?? null,
                    ];

                    foreach ($preferences as $rank => $groupId) {
                        if (!empty($groupId)) {
                            GroupPreferences::create([
                                'student_id' => $student->id,
                                'group_id' => $groupId,
                                'rank' => $rank,
                            ]);
                        }
                    }
                }
            }

            return redirect(route('workshops.show', $workshop->id))
                ->with('success', 'Workshop updated successfully!');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop)
    {
        // Authorization check
        if ($workshop->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Gather statistics before deletion
        $groupCount = $workshop->groups()->count();
        $classroomCount = $workshop->classrooms()->count();
        $studentCount = $workshop->students()->count();
        $workshopName = $workshop->name;

        // Delete (cascades handle everything)
        $workshop->delete();

        // Redirect to workshop list
        return redirect()->route('workshops.index')
            ->with('success', "Workshop '{$workshopName}' deleted successfully. Removed {$groupCount} groups, {$classroomCount} classrooms, and {$studentCount} students.");
    }

    /**
     * Clear all student assignments for a workshop.
     */
    public function clearAssignments(Workshop $workshop)
    {
        // Authorization check
        if ($workshop->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Count assignments before clearing
        $assignmentCount = DB::table('groups_students')
            ->whereIn('group_id', $workshop->groups()->pluck('id'))
            ->count();

        // Clear all assignments
        DB::table('groups_students')
            ->whereIn('group_id', $workshop->groups()->pluck('id'))
            ->delete();

        // Update workshop status
        $workshop->update(['assignment_status' => 'none']);

        // Redirect back to assignments tab
        return redirect(route('workshops.show', $workshop->id) . '#assignments')
            ->with('success', "Cleared {$assignmentCount} student assignments.");
    }

    /**
     * Import data from CSV file
     */
    public function import(Request $request, Workshop $workshop)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            // Read CSV file
            $csvData = array_map(function($line) {
                return str_getcsv($line, ';'); // Semicolon separator like Java version
            }, file($path));

            if (empty($csvData)) {
                return redirect(route('workshops.show', $workshop->id))
                    ->withErrors(['error' => 'CSV file is empty']);
            }

            return DB::transaction(function () use ($csvData, $workshop) {
                // First row is headers
                $headers = array_shift($csvData);

                // Group names start from column 2 (index 2)
                $groupNames = array_slice($headers, 2);

                // Filter out empty group names and extra columns
                $groupNames = array_filter($groupNames, function($name) {
                    return !empty(trim($name)) && !in_array(strtolower(trim($name)), ['kontrollleiste', '']);
                });

                // Create groups with default values
                $groups = [];
                foreach ($groupNames as $index => $groupName) {
                    $groupName = trim($groupName);
                    if (!empty($groupName)) {
                        $groups[$index + 2] = Group::create([ // Keep original column index
                            'workshop_id' => $workshop->id,
                            'name' => $groupName,
                            'minimumParticipants' => 8,
                            'maximumParticipants' => 15,
                            'priorityGroup' => 1,
                        ]);
                    }
                }

                // Track classrooms
                $classrooms = [];

                // Process each student row
                foreach ($csvData as $row) {
                    if (count($row) < 2) continue; // Skip invalid rows

                    $classroomName = trim($row[0] ?? '');
                    $studentName = trim($row[1] ?? '');

                    // Skip empty rows
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

                return redirect(route('workshops.show', $workshop->id))
                    ->with('success', 'CSV imported successfully! Created ' . count($groups) . ' groups, ' . count($classrooms) . ' classrooms, and their students.');
            });

        } catch (\Exception $e) {
            return redirect(route('workshops.show', $workshop->id))
                ->withErrors(['error' => 'Error importing CSV: ' . $e->getMessage()]);
        } finally {
            // Delete the uploaded file
            if (isset($file)) {
                @unlink($file->getRealPath());
            }
        }
    }

    /**
     * Run the assignment algorithm to assign students to groups
     */
    public function runAssignmentAlgorithm(Request $request, Workshop $workshop)
    {
        // Validate preconditions
        if ($workshop->groups()->count() === 0) {
            return redirect(route('workshops.show', $workshop->id))
                ->withErrors(['error' => 'Cannot run algorithm: No groups defined']);
        }

        if ($workshop->students()->count() === 0) {
            return redirect(route('workshops.show', $workshop->id))
                ->withErrors(['error' => 'Cannot run algorithm: No students defined']);
        }

        return DB::transaction(function () use ($workshop) {
            // Clear existing assignments
            DB::table('groups_students')
                ->whereIn('group_id', $workshop->groups()->pluck('id'))
                ->delete();

            // Run the assignment algorithm
            $assignmentService = new AssignmentService();
            $result = $assignmentService->assignStudentsToGroups($workshop);

            // Save assignments to database
            $now = now();
            $userId = auth()->id();

            foreach ($result->assignments as $assignment) {
                DB::table('groups_students')->insert([
                    'student_id' => $assignment['student_id'],
                    'group_id' => $assignment['group_id'],
                    'assignment_method' => 'algorithm',
                    'assigned_at' => $now,
                    'assigned_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Update workshop status
            $workshop->update(['assignment_status' => 'generated']);

            // Build success message
            $assignedCount = $result->getAssignedCount();
            $unassignedCount = $result->getUnassignedCount();
            $groupCount = $result->groups->count();

            $message = "Algorithm completed! {$assignedCount} students assigned to {$groupCount} groups.";

            if ($unassignedCount > 0) {
                $message .= " WARNING: {$unassignedCount} students could not be assigned due to capacity constraints.";
            }

            // Add warnings to session for display
            if ($result->hasWarnings()) {
                session()->flash('assignment_warnings', $result->warnings);
            }

            return redirect(route('workshops.show', $workshop->id) . '#assignments')
                ->with('success', $message);
        });
    }

    /**
     * Update a student's group assignment
     */
    public function updateStudentAssignment(Request $request, Workshop $workshop, Student $student)
    {
        $request->validate([
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        $groupId = $request->input('group_id');

        // Verify group belongs to this workshop
        $group = Group::where('id', $groupId)
            ->where('workshop_id', $workshop->id)
            ->firstOrFail();

        return DB::transaction(function () use ($workshop, $student, $group, $request) {
            // Detach student from any current groups in this workshop
            $workshopGroupIds = $workshop->groups()->pluck('id');
            $student->groups()->detach($workshopGroupIds);

            // Attach to new group with metadata
            $student->groups()->attach($group->id, [
                'assignment_method' => 'manual',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);

            // Update workshop status to manually_edited
            $workshop->update(['assignment_status' => 'manually_edited']);

            // Support both form submission and AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student assignment updated successfully'
                ]);
            }

            return redirect(route('workshops.show', $workshop->id) . '#assignments')
                ->with('success', 'Student assignment updated successfully');
        });
    }

    /**
     * Export workshop assignments to CSV or Excel
     */
    public function exportAssignments(Request $request, Workshop $workshop)
    {
        $format = $request->query('format', 'csv'); // Default to CSV

        // Generate filename with workshop name and timestamp
        $baseFilename = 'assignments-' . \Illuminate\Support\Str::slug($workshop->name) . '-' . now()->format('Y-m-d');

        // Export based on format
        if ($format === 'xlsx' || $format === 'xls') {
            $extension = $format === 'xls' ? 'xls' : 'xlsx';
            $filename = "{$baseFilename}.{$extension}";

            // Create export instance (delimiter not needed for Excel)
            $export = new AssignmentsExport($workshop);

            return Excel::download($export, $filename,
                $format === 'xls' ? \Maatwebsite\Excel\Excel::XLS : \Maatwebsite\Excel\Excel::XLSX
            );
        } else {
            // CSV export with locale-aware delimiter
            $filename = "{$baseFilename}.csv";

            // Determine CSV delimiter based on locale
            $locale = app()->getLocale();
            $europeanLocales = ['de', 'fr', 'es', 'it', 'pt', 'nl', 'pl', 'cs', 'el', 'hu', 'ro', 'sv', 'da', 'no', 'fi'];
            $delimiter = in_array($locale, $europeanLocales) ? ';' : ',';

            // Create export instance with delimiter
            $export = new AssignmentsExport($workshop, $delimiter);

            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
        }
    }
}
