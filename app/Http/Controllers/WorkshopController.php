<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Group;
use App\Models\GroupPreferences;
use App\Models\Student;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
            'groupNames.*' => 'nullable|string|max:255',
            'minimumParticipants.*' => 'required|integer|min:0',
            'maximumParticipants.*' => 'required|integer|min:0',
            'priorityGroups.*' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $workshop = Workshop::create([
                'name' => $request->input('name'),
                'user_id' => auth()->id(),
            ]);

            $groupNames = $request->input('groupNames');
            $minimumParticipants = $request->input('minimumParticipants');
            $maximumParticipants = $request->input('maximumParticipants');
            $priorityGroups = $request->input('priorityGroups');

            $newGroups = new Collection();

            for ($i = 0; $i < count($groupNames); $i++) {
                if(!empty($groupNames[$i])){
                    $min = (int) $minimumParticipants[$i];
                    $max = (int) $maximumParticipants[$i];

                    // Validate min <= max
                    if ($min > $max) {
                        return redirect(route('workshops.create'))
                            ->withErrors(['error' => "Group '{$groupNames[$i]}': Minimum participants cannot be greater than maximum participants."])
                            ->withInput();
                    }

                    $newGroups->push(
                        Group::create([
                            'workshop_id' => $workshop->id,
                            'name' => $groupNames[$i],
                            'minimumParticipants' => $min,
                            'maximumParticipants' => $max,
                            'priorityGroup' => $priorityGroups[$i],

                        ])
                    );
                }
            }
            $workshop->groups()->saveMany($newGroups);
            return redirect(route('workshops.show', $workshop->id));

        });

    }


    /**
     * Display the specified resource.
     */
    public function show(Workshop $workshop)
    {
        return view('workshops.show', ['workshop' => $workshop]);
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
        //
    }
}
