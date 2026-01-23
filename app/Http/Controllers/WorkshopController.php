<?php

namespace App\Http\Controllers;

use App\Models\Group;
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
                    $newGroups->push(
                        Group::create([
                            'workshop_id' => $workshop->id,
                            'name' => $groupNames[$i],
                            'minimumParticipants' => $minimumParticipants[$i],
                            'maximumParticipants' => $maximumParticipants[$i],
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
        abort(400, 'Not implemented yet');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop)
    {
        //
    }
}
