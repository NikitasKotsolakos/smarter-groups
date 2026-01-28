<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8" x-data="{ activeTab: 'groups' }">
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

            <div class="form-group mb-6">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name', $workshop->name) }}" required>
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Tab Headers -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8">
                    <button type="button" @click="activeTab = 'groups'"
                            :class="activeTab === 'groups' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Groups ({{ $workshop->groups->count() }})
                    </button>
                    <button type="button" @click="activeTab = 'classrooms'"
                            :class="activeTab === 'classrooms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Classrooms ({{ $workshop->classrooms->count() }})
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-4">
                <!-- Groups Tab -->
                <div x-show="activeTab === 'groups'" x-cloak>
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
                </div>

                <!-- Classrooms Tab -->
                <div x-show="activeTab === 'classrooms'" x-cloak>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Classroom Name</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($workshop->classrooms as $index => $classroom)
                            <tr>
                                <td>
                                    <input type="hidden" name="classroomIds[]" value="{{$classroom->id}}">
                                    <input type="text" name="classroomNames[]" class="form-control @error('classroomNames.'.$index) border-red-500 @enderror" value="{{ old('classroomNames.'.$index, $classroom->name) }}">
                                    @error('classroomNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td class="w-32">
                                    <!-- Placeholder for future delete button -->
                                </td>
                            </tr>
                        @endforeach
                        <!-- Add new classroom rows -->
                        @foreach (range(0, 5) as $index)
                            <tr>
                                <td>
                                    <input type="text" name="newClassroomNames[]" class="form-control @error('newClassroomNames.'.$index) border-red-500 @enderror" value="{{ old('newClassroomNames.'.$index) }}" placeholder="New classroom">
                                    @error('newClassroomNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <!-- Placeholder -->
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group mt-6">
                <button class="btn btn-danger" type="submit">
                    Update Workshop
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
