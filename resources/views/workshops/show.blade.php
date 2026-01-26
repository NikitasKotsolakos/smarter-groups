<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form autocomplete="off" method="POST" action="{{ route("workshops.update", $workshop->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name', $workshop->name) }}" required>
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
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
                @foreach ($workshop->groups as $index => $group)
                    <tr>
                        <td>
                            <input type="hidden" name="groupIds[]" value="{{$group->id}}">
                            <input type="text" name="groupNames[]" class="form-control @error('groupNames.'.$index) border-red-500 @enderror" value="{{ old('groupNames.'.$index, $group->name) }}">
                            @error('groupNames.'.$index)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="minimumParticipants[]" class="form-control @error('minimumParticipants.'.$index) border-red-500 @enderror" value="{{ old('minimumParticipants.'.$index, $group->minimumParticipants) }}" >
                            @error('minimumParticipants.'.$index)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="maximumParticipants[]" class="form-control @error('maximumParticipants.'.$index) border-red-500 @enderror" value="{{ old('maximumParticipants.'.$index, $group->maximumParticipants) }}" >
                            @error('maximumParticipants.'.$index)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="priorityGroups[]" class="form-control @error('priorityGroups.'.$index) border-red-500 @enderror" value="{{ old('priorityGroups.'.$index, $group->priorityGroup) }}" >
                            @error('priorityGroups.'.$index)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
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
