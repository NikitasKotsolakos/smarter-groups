<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Workshop;
use Illuminate\Http\Request;

class GroupController extends Controller
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
    public function show(Group $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop, Group $group)
    {
        // Verify group belongs to workshop
        if ($group->workshop_id !== $workshop->id) {
            abort(404, 'Group not found in this workshop.');
        }

        // Authorization check
        if ($workshop->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Count assigned students
        $assignedStudentCount = $group->students()->count();
        $groupName = $group->name;

        // Delete group (cascades to preferences and assignments)
        $group->delete();

        // Redirect back to groups tab
        return redirect(route('workshops.show', $workshop->id) . '#groups')
            ->with('success', "Group '{$groupName}' deleted successfully. {$assignedStudentCount} students were unassigned.");
    }
}
