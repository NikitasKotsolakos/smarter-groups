<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form autocomplete="off" method="PUT" action="{{ route("workshops.update", $workshop->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control" type="text" name="name" value="{{$workshop->name}}" required>
                <span class="help-block"></span>
            </div>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Groups</th>
                    <th>Minimum Participants</th>
                    <th>Maximum Participants</th>
                    <th>Priority Group</th>

                </tr>
                </thead>
                <tbody>
                @foreach ($workshop->groups as $group)
                    <tr>
                        <td><input type="text" name="groupNames[]" class="form-control" value="{{$group->name}}"></td>
                        <td><input type="number" name="minimumParticipants[]" class="form-control" value="{{$group->minimumParticipants}}" ></td>
                        <td><input type="number" name="maximumParticipants[]" class="form-control" value="{{$group->maximumParticipants}}" ></td>
                        <td><input type="number" name="priorityGroups[]" class="form-control" value="{{$group->priorityGroup}}" ></td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    Update
                </button>
            </div>
        </form>    </div>
</x-app-layout>
