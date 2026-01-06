<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Workshop;
use Illuminate\Http\Request;

class StudentController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop, Student $student)
    {
        // Verify student belongs to workshop (through classroom)
        $classroom = $student->classroom;
        if (!$classroom || $classroom->workshop_id !== $workshop->id) {
            abort(404, 'Student not found in this workshop.');
        }

        // Authorization check
        if ($workshop->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Store student name
        $studentName = $student->name;

        // Delete student (cascades to preferences and assignments)
        $student->delete();

        // Redirect back to students tab
        return redirect(route('workshops.show', $workshop->id) . '#students')
            ->with('success', "Student '{$studentName}' deleted successfully.");
    }
}
