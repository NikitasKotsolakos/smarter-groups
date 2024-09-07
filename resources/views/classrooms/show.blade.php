<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form autocomplete="off" method="PUT" action="{{ route("workshops.classrooms.update", [ 'workshop' => $workshop, 'classroom' => $classroom]) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">Classroom Name</label>
                <input class="form-control" type="text" name="name" required>
            </div>
            <div class="form-group">
                <label class="required" for="name">Grade</label>
                <input class="form-control" type="text" name="grade" required>
            </div>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Group Preference</th>
                    <th>Group Preference</th>
                    <th>Group Preference</th>
                    <th>Group Preference</th>
                    <th>Group Preference</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($classroom->students as $student)
                    {{dump($classroom)}}

                    <tr>
                        <td><input type="text" name="studentNames[]" class="form-control" value="{{$student->name}}"></td>
                        <td><select  name="preferences1[]" class="form-control" >
                                <option value="{{$student->group_preferences}}" selected></option>
                                @foreach($workshop->groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                            @endforeach
                        </td>
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
