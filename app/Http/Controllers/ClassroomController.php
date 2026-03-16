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

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Workshop $workshop) : View
    {
        $groups = Group::whereBelongsTo($workshop)->get();
        dump($groups);
        return view('classrooms.create', ['workshop' => $workshop, 'groups' => $groups]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Workshop $workshop)
    {
        return DB::transaction(function () use ($request, $workshop) {
            $newclassroom = Classroom::create([
                'name' => $request->input('name'),
                'grade' => $request->input('grade'),
                'workshop_id' => $workshop->id,
            ]);

            $studentNames = $request->input('studentNames');

            $newStudents = new Collection();

            for ($i = 0; $i < count($studentNames); $i++) {
                if(!empty($studentNames[$i])){
                    $newclassroom->students()->create([
                            'name' => $studentNames[$i],
                        ]);
                }
            }

            $createdClassroom = Classroom::whereId($newclassroom->id)->first();

            for ($i = 0; $i < count($studentNames); $i++) {
                if(!empty($studentNames[$i])) {
                    $student = Student::whereBelongsTo($newclassroom)->whereName($studentNames[$i])->first();
                    $preferences = new Collection();
                    if (!empty($request->input('preferences1')[$i])) {
                        GroupPreferences::create([
                            'student_id' => $student->id,
                            'group_id' => $request->input('preferences1')[$i],
                        ]);
                    }
                    if (!empty($request->input('preferences2')[$i])) {
                        GroupPreferences::create([
                            'student_id' => $student->id,
                            'group_id' => $request->input('preferences2')[$i],
                        ]);
                    }
                    if (!empty($request->input('preferences3')[$i])) {
                        GroupPreferences::create([
                            'student_id' => $student->id,
                            'group_id' => $request->input('preferences3')[$i],
                        ]);
                    }
                    if (!empty($request->input('preferences4')[$i])) {
                        GroupPreferences::create([
                            'student_id' => $student->id,
                            'group_id' => $request->input('preferences4')[$i],
                        ]);
                    }
                    if (!empty($request->input('preferences5')[$i])) {
                        GroupPreferences::create([
                            'student_id' => $student->id,
                            'group_id' => $request->input('preferences5')[$i],
                        ]);
                    }

                    $student->groupPreferences()->saveMany($preferences);
                }
            }

            $createdClassroom->students->load('groupPreferences'); //TODO not working
            return redirect(route('workshops.classrooms.show', ['workshop' => $workshop, 'classroom' => $createdClassroom]));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Workshop $workshop, Classroom $classroom)
    {
        return view('classrooms.show', ['workshop' => $workshop, 'classroom' => $classroom]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classroom $classroom)
    {
        abort(400, 'Not implemented yet');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        // Get workshop for authorization and redirect
        $workshop = $classroom->workshop;

        // Authorization check
        if ($workshop->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Count students that will be deleted
        $studentCount = $classroom->students()->count();
        $classroomName = $classroom->name;

        // Delete classroom (cascades to students, preferences, assignments)
        $classroom->delete();

        // Redirect back to classrooms tab
        return redirect(route('workshops.show', $workshop->id) . '#classrooms')
            ->with('success', "Classroom '{$classroomName}' and {$studentCount} students deleted successfully.");
    }
}
