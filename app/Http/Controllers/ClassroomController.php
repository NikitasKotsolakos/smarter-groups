<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Group;
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
            $classroom = Classroom::create([
                'name' => $request->input('name'),
                'grade' => $request->input('grade'),
                'workshop_id' => $workshop->id,
            ]);

            $studentNames = $request->input('studentNames');

            $newStudents = new Collection();

            for ($i = 0; $i < count($studentNames); $i++) {
                if(!empty($studentNames[$i])){
                    $newStudents->push(
                        Student::create([
                            'classrroom_id' => $classroom->id,
                            'name' => $studentNames[$i],
                        ])
                    );

                }
            }
            $classroom->students()->saveMany($newStudents);
            //TODO save preferences too

            return redirect(route('workshops.classrooms.show', ['workshop' => $workshop, 'classroom' => $classroom]));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom)
    {
        return view('classrooms.show', ['classroom' => $classroom]);
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
        //
    }
}
