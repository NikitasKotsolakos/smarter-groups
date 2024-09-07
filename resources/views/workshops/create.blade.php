<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form autocomplete="off" method="POST" action="{{ route("workshops.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control" type="text" name="name" required>
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
                    @foreach (range(0, 20) as $index)
                        <tr>
                            <td><input type="text" name="groupNames[]" class="form-control" value=""></td>
                            <td><input type="number" name="minimumParticipants[]" class="form-control" value="10" ></td>
                            <td><input type="number" name="maximumParticipants[]" class="form-control" value="20" ></td>
                            <td><input type="number" name="priorityGroups[]" class="form-control" value="1" ></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    Save
                </button>
            </div>
        </form>    </div>
</x-app-layout>
