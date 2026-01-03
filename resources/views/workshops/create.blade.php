<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form autocomplete="off" method="POST" action="{{ route("workshops.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}" required>
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
                    @foreach (range(0, 20) as $index)
                        <tr>
                            <td><input type="text" name="groupNames[]" class="form-control @error('groupNames.'.$index) border-red-500 @enderror" value="{{ old('groupNames.'.$index) }}"></td>
                            <td><input type="number" name="minimumParticipants[]" class="form-control @error('minimumParticipants.'.$index) border-red-500 @enderror" value="{{ old('minimumParticipants.'.$index, 10) }}" ></td>
                            <td><input type="number" name="maximumParticipants[]" class="form-control @error('maximumParticipants.'.$index) border-red-500 @enderror" value="{{ old('maximumParticipants.'.$index, 20) }}" ></td>
                            <td><input type="number" name="priorityGroups[]" class="form-control @error('priorityGroups.'.$index) border-red-500 @enderror" value="{{ old('priorityGroups.'.$index, 1) }}" ></td>
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
