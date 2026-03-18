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
            <label class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="handleFileSelect(this)">
                Import from CSV
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

        <!-- Main Workshop Update Form -->
        <form id="workshop-update-form" autocomplete="off" method="POST" action="{{ route('workshops.update', $workshop->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <x-input-label for="name" :value="__('Workshop Name')" class="required" />
                <input type="text" id="name" name="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full @error('name') border-red-500 @enderror" value="{{ old('name', $workshop->name) }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
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
                    <x-table>
                        <x-table-header>
                            <x-table-row :hover="false">
                                <x-table-heading>Groups</x-table-heading>
                                <x-table-heading>Minimum Participants</x-table-heading>
                                <x-table-heading>Maximum Participants</x-table-heading>
                                <x-table-heading>Priority Group</x-table-heading>
                                <x-table-heading class="w-32">Actions</x-table-heading>
                            </x-table-row>
                        </x-table-header>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($workshop->groups as $index => $group)
                            <x-table-row>
                                <x-table-data>
                                    <input type="hidden" name="groupIds[]" value="{{$group->id}}">
                                    <input type="text" name="groupNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('groupNames.'.$index) border-red-500 @enderror" value="{{ old('groupNames.'.$index, $group->name) }}">
                                    @error('groupNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="minimumParticipants[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('minimumParticipants.'.$index) border-red-500 @enderror" value="{{ old('minimumParticipants.'.$index, $group->minimumParticipants) }}">
                                    @error('minimumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="maximumParticipants[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('maximumParticipants.'.$index) border-red-500 @enderror" value="{{ old('maximumParticipants.'.$index, $group->maximumParticipants) }}">
                                    @error('maximumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="priorityGroups[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('priorityGroups.'.$index) border-red-500 @enderror" value="{{ old('priorityGroups.'.$index, $group->priorityGroup) }}">
                                    @error('priorityGroups.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data class="w-32 text-center">
                                    <button type="button"
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-group-deletion-{{ $group->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </x-table-data>
                            </x-table-row>
                        @endforeach
                        <!-- Add new group rows -->
                        @foreach (range(0, 9) as $index)
                            <x-table-row>
                                <x-table-data>
                                    <input type="text" name="newGroupNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newGroupNames.'.$index) border-red-500 @enderror" value="{{ old('newGroupNames.'.$index) }}" placeholder="New group">
                                    @error('newGroupNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="newMinimumParticipants[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newMinimumParticipants.'.$index) border-red-500 @enderror" value="{{ old('newMinimumParticipants.'.$index, 10) }}" placeholder="10">
                                    @error('newMinimumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="newMaximumParticipants[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newMaximumParticipants.'.$index) border-red-500 @enderror" value="{{ old('newMaximumParticipants.'.$index, 20) }}" placeholder="20">
                                    @error('newMaximumParticipants.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <input type="number" name="newPriorityGroups[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newPriorityGroups.'.$index) border-red-500 @enderror" value="{{ old('newPriorityGroups.'.$index, 1) }}" placeholder="1">
                                    @error('newPriorityGroups.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data><!-- No delete for new rows --></x-table-data>
                            </x-table-row>
                        @endforeach
                        </tbody>
                    </x-table>
                </div>

                <!-- Classrooms Tab -->
                <div x-show="activeTab === 'classrooms'" x-cloak>
                    <x-table>
                        <x-table-header>
                            <x-table-row :hover="false">
                                <x-table-heading>Classroom Name</x-table-heading>
                                <x-table-heading>Actions</x-table-heading>
                            </x-table-row>
                        </x-table-header>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($workshop->classrooms as $index => $classroom)
                            <x-table-row>
                                <x-table-data>
                                    <input type="hidden" name="classroomIds[]" value="{{$classroom->id}}">
                                    <input type="text" name="classroomNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('classroomNames.'.$index) border-red-500 @enderror" value="{{ old('classroomNames.'.$index, $classroom->name) }}">
                                    @error('classroomNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data class="w-32 text-center">
                                    <button type="button"
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-classroom-deletion-{{ $classroom->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </x-table-data>
                            </x-table-row>
                        @endforeach
                        <!-- Add new classroom rows -->
                        @foreach (range(0, 5) as $index)
                            <x-table-row>
                                <x-table-data>
                                    <input type="text" name="newClassroomNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newClassroomNames.'.$index) border-red-500 @enderror" value="{{ old('newClassroomNames.'.$index) }}" placeholder="New classroom">
                                    @error('newClassroomNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <!-- No delete for new rows -->
                                </x-table-data>
                            </x-table-row>
                        @endforeach
                        </tbody>
                    </x-table>
                </div>

                <!-- Students Tab -->
                <div x-show="activeTab === 'students'" x-cloak>
                    <x-table>
                        <x-table-header>
                            <x-table-row :hover="false">
                                <x-table-heading>Student Name</x-table-heading>
                                <x-table-heading>Classroom</x-table-heading>
                                <x-table-heading>1st Choice</x-table-heading>
                                <x-table-heading>2nd Choice</x-table-heading>
                                <x-table-heading>3rd Choice</x-table-heading>
                                <x-table-heading class="w-32">Actions</x-table-heading>
                            </x-table-row>
                        </x-table-header>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($workshop->students as $index => $student)
                            <x-table-row>
                                <x-table-data>
                                    <input type="hidden" name="studentIds[]" value="{{$student->id}}">
                                    <input type="text" name="studentNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('studentNames.'.$index) border-red-500 @enderror" value="{{ old('studentNames.'.$index, $student->name) }}">
                                    @error('studentNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <select name="studentClassrooms[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('studentClassrooms.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                @php
                                    $prefs = $student->groupPreferences->keyBy('rank');
                                @endphp
                                <x-table-data>
                                    <select name="studentPreference1[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('studentPreference1.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data>
                                    <select name="studentPreference2[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('studentPreference2.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data>
                                    <select name="studentPreference3[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('studentPreference3.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data class="w-32 text-center">
                                    <button type="button"
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-student-deletion-{{ $student->id }}')"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </x-table-data>
                            </x-table-row>
                        @endforeach
                        <!-- Add new student rows -->
                        @foreach (range(0, 9) as $index)
                            <x-table-row>
                                <x-table-data>
                                    <input type="text" name="newStudentNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newStudentNames.'.$index) border-red-500 @enderror" value="{{ old('newStudentNames.'.$index) }}" placeholder="New student">
                                    @error('newStudentNames.'.$index)
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </x-table-data>
                                <x-table-data>
                                    <select name="newStudentClassrooms[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newStudentClassrooms.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data>
                                    <select name="newStudentPreference1[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newStudentPreference1.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data>
                                    <select name="newStudentPreference2[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newStudentPreference2.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data>
                                    <select name="newStudentPreference3[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full @error('newStudentPreference3.'.$index) border-red-500 @enderror">
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
                                </x-table-data>
                                <x-table-data><!-- No delete for new rows --></x-table-data>
                            </x-table-row>
                        @endforeach
                        </tbody>
                    </x-table>
                </div>
            </div>

            <div class="mt-6" x-show="activeTab !== 'assignments'" x-cloak>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-workshop-deletion')"
                >Delete Workshop</x-danger-button>
            </section>
        </div>

        <!-- Assignments Tab (outside main form) -->
        <div x-show="activeTab === 'assignments'" x-cloak>
            @if($workshop->hasAssignments())
                @include('workshops.partials.assignments-display')
            @else
                @include('workshops.partials.assignments-empty-state')
            @endif
        </div>
    </div>

    {{-- All Delete Modals - OUTSIDE the main form --}}

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
</x-app-layout>
