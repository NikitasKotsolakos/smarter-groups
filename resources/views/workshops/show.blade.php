<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8" x-data="{
        activeTab: window.location.hash ? window.location.hash.substring(1) : 'groups',
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
        }
    }">
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

        <!-- CSV Import Form (separate form) -->
        <form method="POST" action="{{ route('workshops.import', $workshop->id) }}" enctype="multipart/form-data" class="mb-4" onsubmit="return confirmImport(event)">
            @csrf
            <label class="btn bg-blue-500 hover:bg-blue-600 text-white cursor-pointer inline-block">
                <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="handleFileSelect(this)">
                📁 Import from CSV
            </label>
            <span class="text-gray-600 text-sm ml-2">Upload CSV with students & preferences (will replace all existing data)</span>
        </form>

        <script>
            let selectedFile = null;

            function handleFileSelect(input) {
                selectedFile = input.files[0];
                if (selectedFile) {
                    input.form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }

            function confirmImport(event) {
                if (!selectedFile) {
                    event.preventDefault();
                    return false;
                }

                const hasData = {{ ($workshop->groups->count() > 0 || $workshop->classrooms->count() > 0 || $workshop->students->count() > 0) ? 'true' : 'false' }};

                if (hasData) {
                    const message = 'WARNING: This will DELETE all existing data in this workshop:\n\n' +
                                  '• {{ $workshop->groups->count() }} groups\n' +
                                  '• {{ $workshop->classrooms->count() }} classrooms\n' +
                                  '• {{ $workshop->students->count() }} students\n' +
                                  '• All group preferences and assignments\n\n' +
                                  'This action cannot be undone. Continue with import?';

                    if (!confirm(message)) {
                        event.preventDefault();
                        selectedFile = null;
                        event.target.reset();
                        return false;
                    }
                }

                return true;
            }
        </script>

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
                    <button type="button" @click="setTab('groups')"
                            :class="activeTab === 'groups' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Groups ({{ $workshop->groups->count() }})
                    </button>
                    <button type="button" @click="setTab('classrooms')"
                            :class="activeTab === 'classrooms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Classrooms ({{ $workshop->classrooms->count() }})
                    </button>
                    <button type="button" @click="setTab('students')"
                            :class="activeTab === 'students' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Students ({{ $workshop->students->count() }})
                    </button>
                    <button type="button" @click="setTab('assignments')"
                            :class="activeTab === 'assignments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Assignments
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
                            <th class="w-32">Actions</th>
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
                                <td class="w-32 text-center">
                                    <button type="button"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-group-deletion-{{ $group->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Add new group rows -->
                        @foreach (range(0, 9) as $index)
                            <tr>
                                <td>
                                    <input type="text" name="newGroupNames[]" class="form-control @error('newGroupNames.'.$index) border-red-500 @enderror" value="{{ old('newGroupNames.'.$index) }}" placeholder="New group">
                                    @error('newGroupNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" name="newMinimumParticipants[]" class="form-control @error('newMinimumParticipants.'.$index) border-red-500 @enderror" value="{{ old('newMinimumParticipants.'.$index, 10) }}" placeholder="10">
                                    @error('newMinimumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" name="newMaximumParticipants[]" class="form-control @error('newMaximumParticipants.'.$index) border-red-500 @enderror" value="{{ old('newMaximumParticipants.'.$index, 20) }}" placeholder="20">
                                    @error('newMaximumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" name="newPriorityGroups[]" class="form-control @error('newPriorityGroups.'.$index) border-red-500 @enderror" value="{{ old('newPriorityGroups.'.$index, 1) }}" placeholder="1">
                                    @error('newPriorityGroups.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td><!-- No delete for new rows --></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Delete modals for groups --}}
                    @foreach ($workshop->groups as $group)
                        <x-modal name="confirm-group-deletion-{{ $group->id }}" focusable>
                            <form method="post" action="{{ route('workshops.groups.destroy', [$workshop->id, $group->id]) }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">
                                    Delete group "{{ $group->name }}"?
                                </h2>

                                <p class="mt-3 text-sm text-gray-600">
                                    This will remove the group and unassign
                                    <strong>{{ $group->students()->count() }}</strong> student(s).
                                    Their preferences for this group will also be deleted.
                                </p>

                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                        Cancel
                                    </x-secondary-button>

                                    <x-danger-button>
                                        Delete Group
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
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
                                <td class="w-32 text-center">
                                    <button type="button"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-classroom-deletion-{{ $classroom->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
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
                                    <!-- No delete for new rows -->
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Delete modals for classrooms --}}
                    @foreach ($workshop->classrooms as $classroom)
                        <x-modal name="confirm-classroom-deletion-{{ $classroom->id }}" focusable>
                            <form method="post" action="{{ route('workshops.classrooms.destroy', [$workshop->id, $classroom->id]) }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">
                                    Delete classroom "{{ $classroom->name }}"?
                                </h2>

                                <p class="mt-3 text-sm text-gray-600">
                                    This will permanently delete the classroom and all
                                    <strong>{{ $classroom->students()->count() }}</strong> student(s) in it.
                                </p>

                                <p class="mt-2 text-sm text-red-600 font-medium">
                                    All student data, preferences, and assignments will be lost.
                                </p>

                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                        Cancel
                                    </x-secondary-button>

                                    <x-danger-button>
                                        Delete Classroom & Students
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
                </div>

                <!-- Students Tab -->
                <div x-show="activeTab === 'students'" x-cloak>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Classroom</th>
                            <th>1st Choice</th>
                            <th>2nd Choice</th>
                            <th>3rd Choice</th>
                            <th class="w-32">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($workshop->students as $index => $student)
                            <tr>
                                <td>
                                    <input type="hidden" name="studentIds[]" value="{{$student->id}}">
                                    <input type="text" name="studentNames[]" class="form-control @error('studentNames.'.$index) border-red-500 @enderror" value="{{ old('studentNames.'.$index, $student->name) }}">
                                    @error('studentNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="studentClassrooms[]" class="form-control @error('studentClassrooms.'.$index) border-red-500 @enderror">
                                        <option value="">-- Select Classroom --</option>
                                        @foreach ($workshop->classrooms as $classroom)
                                            <option value="{{ $classroom->id }}" {{ old('studentClassrooms.'.$index, $student->classroom_id) == $classroom->id ? 'selected' : '' }}>
                                                {{ $classroom->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('studentClassrooms.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                @php
                                    $prefs = $student->groupPreferences->keyBy('rank');
                                @endphp
                                <td>
                                    <select name="studentPreference1[]" class="form-control @error('studentPreference1.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('studentPreference1.'.$index, $prefs->get(1)?->group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('studentPreference1.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="studentPreference2[]" class="form-control @error('studentPreference2.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('studentPreference2.'.$index, $prefs->get(2)?->group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('studentPreference2.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="studentPreference3[]" class="form-control @error('studentPreference3.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('studentPreference3.'.$index, $prefs->get(3)?->group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('studentPreference3.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td class="w-32 text-center">
                                    <button type="button"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-student-deletion-{{ $student->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Add new student rows -->
                        @foreach (range(0, 9) as $index)
                            <tr>
                                <td>
                                    <input type="text" name="newStudentNames[]" class="form-control @error('newStudentNames.'.$index) border-red-500 @enderror" value="{{ old('newStudentNames.'.$index) }}" placeholder="New student">
                                    @error('newStudentNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="newStudentClassrooms[]" class="form-control @error('newStudentClassrooms.'.$index) border-red-500 @enderror">
                                        <option value="">-- Select Classroom --</option>
                                        @foreach ($workshop->classrooms as $classroom)
                                            <option value="{{ $classroom->id }}" {{ old('newStudentClassrooms.'.$index) == $classroom->id ? 'selected' : '' }}>
                                                {{ $classroom->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('newStudentClassrooms.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="newStudentPreference1[]" class="form-control @error('newStudentPreference1.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('newStudentPreference1.'.$index) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('newStudentPreference1.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="newStudentPreference2[]" class="form-control @error('newStudentPreference2.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('newStudentPreference2.'.$index) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('newStudentPreference2.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <select name="newStudentPreference3[]" class="form-control @error('newStudentPreference3.'.$index) border-red-500 @enderror">
                                        <option value="">-- None --</option>
                                        @foreach ($workshop->groups as $group)
                                            <option value="{{ $group->id }}" {{ old('newStudentPreference3.'.$index) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('newStudentPreference3.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td><!-- No delete for new rows --></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Delete modals for students --}}
                    @foreach ($workshop->students as $student)
                        <x-modal name="confirm-student-deletion-{{ $student->id }}" focusable>
                            <form method="post" action="{{ route('workshops.students.destroy', [$workshop->id, $student->id]) }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">
                                    Delete student "{{ $student->name }}"?
                                </h2>

                                <p class="mt-3 text-sm text-gray-600">
                                    This will permanently delete the student and all their preferences and group assignments.
                                </p>

                                <div class="mt-6 flex justify-end gap-3">
                                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                        Cancel
                                    </x-secondary-button>

                                    <x-danger-button>
                                        Delete Student
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
                </div>

            </div>

            <div class="form-group mt-6" x-show="activeTab !== 'assignments'" x-cloak>
                <button class="btn btn-danger" type="submit">
                    Update Workshop
                </button>
            </div>
        </form>

        {{-- Delete Workshop Section --}}
        <div class="mt-8 pt-6 border-t border-gray-200" x-show="activeTab !== 'assignments'" x-cloak>
            <section class="space-y-4">
                <header>
                    <h3 class="text-lg font-medium text-gray-900">Delete Workshop</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Permanently delete this workshop and all its data. This action cannot be undone.
                    </p>
                </header>

                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-workshop-deletion')"
                >Delete Workshop</x-danger-button>
            </section>
        </div>

        {{-- Delete Workshop Modal --}}
        <x-modal name="confirm-workshop-deletion" focusable>
            <form method="post" action="{{ route('workshops.destroy', $workshop->id) }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this workshop?
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    This will permanently delete:
                </p>
                <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                    <li><strong>{{ $workshop->groups->count() }}</strong> groups</li>
                    <li><strong>{{ $workshop->classrooms->count() }}</strong> classrooms</li>
                    <li><strong>{{ $workshop->students->count() }}</strong> students</li>
                    <li>All group preferences and assignments</li>
                </ul>

                <p class="mt-4 text-sm text-red-600 font-medium">
                    This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>

                    <x-danger-button>
                        Delete Workshop
                    </x-danger-button>
                </div>
            </form>
        </x-modal>

        <!-- Assignments Tab (outside main form to avoid nesting) -->
        <div x-show="activeTab === 'assignments'" x-cloak>
            @if($workshop->hasAssignments())
                @include('workshops.partials.assignments-display')
            @else
                @include('workshops.partials.assignments-empty-state')
            @endif
        </div>
    </div>
</x-app-layout>
