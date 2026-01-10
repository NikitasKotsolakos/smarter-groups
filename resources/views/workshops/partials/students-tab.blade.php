{{-- Students Tab Content --}}
{{-- Receives: $workshop --}}

<div x-data="{ newStudentRows: 3 }">
    {{-- Section Header with Update Button --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Manage Students</h3>
        <x-primary-button type="submit">
            Update Students
        </x-primary-button>
    </div>

    @if($workshop->classrooms->count() === 0)
        <x-alert type="warning" class="mb-4">
            Please add at least one classroom before adding students.
        </x-alert>
    @endif

    <x-table>
        <x-table-header>
            <x-table-row :hover="false">
                <x-table-heading>Student Name</x-table-heading>
                <x-table-heading>Classroom</x-table-heading>
                <x-table-heading>1st Choice</x-table-heading>
                <x-table-heading>2nd Choice</x-table-heading>
                <x-table-heading>3rd Choice</x-table-heading>
                <x-table-heading class="w-24">Actions</x-table-heading>
            </x-table-row>
        </x-table-header>
        <tbody class="bg-white divide-y divide-gray-200">
            {{-- Existing Students --}}
            @foreach ($workshop->students as $index => $student)
                @php
                    $prefs = $student->groupPreferences->keyBy('rank');
                @endphp
                <x-table-row>
                    <x-table-data>
                        <input type="hidden" name="studentIds[]" value="{{ $student->id }}">
                        <x-text-input
                            type="text"
                            name="studentNames[]"
                            class="block w-full @error('studentNames.'.$index) border-red-500 @enderror"
                            value="{{ old('studentNames.'.$index, $student->name) }}"
                        />
                        @error('studentNames.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-select
                            name="studentClassrooms[]"
                            class="block w-full @error('studentClassrooms.'.$index) border-red-500 @enderror"
                        >
                            <option value="">-- Select --</option>
                            @foreach ($workshop->classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('studentClassrooms.'.$index, $student->classroom_id) == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('studentClassrooms.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-select
                            name="studentPreference1[]"
                            class="block w-full @error('studentPreference1.'.$index) border-red-500 @enderror"
                        >
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}" {{ old('studentPreference1.'.$index, $prefs->get(1)?->group_id) == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('studentPreference1.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-select
                            name="studentPreference2[]"
                            class="block w-full @error('studentPreference2.'.$index) border-red-500 @enderror"
                        >
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}" {{ old('studentPreference2.'.$index, $prefs->get(2)?->group_id) == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('studentPreference2.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-select
                            name="studentPreference3[]"
                            class="block w-full @error('studentPreference3.'.$index) border-red-500 @enderror"
                        >
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}" {{ old('studentPreference3.'.$index, $prefs->get(3)?->group_id) == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </x-select>
                        @error('studentPreference3.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data class="text-center">
                        <button type="button"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-student-deletion-{{ $student->id }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                                aria-label="Delete student {{ $student->name }}">
                            Delete
                        </button>
                    </x-table-data>
                </x-table-row>
            @endforeach

            {{-- New Student Rows (Dynamic with Alpine.js) --}}
            <template x-for="i in newStudentRows" :key="i">
                <x-table-row class="bg-gray-50/50">
                    <x-table-data>
                        <x-text-input
                            type="text"
                            name="newStudentNames[]"
                            class="block w-full"
                            placeholder="New student name"
                        />
                    </x-table-data>
                    <x-table-data>
                        <x-select name="newStudentClassrooms[]" class="block w-full">
                            <option value="">-- Select --</option>
                            @foreach ($workshop->classrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                            @endforeach
                        </x-select>
                    </x-table-data>
                    <x-table-data>
                        <x-select name="newStudentPreference1[]" class="block w-full">
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </x-select>
                    </x-table-data>
                    <x-table-data>
                        <x-select name="newStudentPreference2[]" class="block w-full">
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </x-select>
                    </x-table-data>
                    <x-table-data>
                        <x-select name="newStudentPreference3[]" class="block w-full">
                            <option value="">-- None --</option>
                            @foreach ($workshop->groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </x-select>
                    </x-table-data>
                    <x-table-data>
                        {{-- No delete for new rows --}}
                    </x-table-data>
                </x-table-row>
            </template>
        </tbody>
    </x-table>

    {{-- Add More Rows Button --}}
    <div class="mt-4 flex justify-center">
        <x-secondary-button type="button" @click="newStudentRows += 3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add 3 More Rows
        </x-secondary-button>
    </div>
</div>
