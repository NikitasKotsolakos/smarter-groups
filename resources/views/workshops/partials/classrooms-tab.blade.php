{{-- Classrooms Tab Content --}}
{{-- Receives: $workshop --}}

<div x-data="{ newClassroomRows: 3 }">
    {{-- Section Header with Update Button --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Manage Classrooms</h3>
        <x-primary-button type="submit">
            Update Classrooms
        </x-primary-button>
    </div>

    <x-table>
        <x-table-header>
            <x-table-row :hover="false">
                <x-table-heading>Classroom Name</x-table-heading>
                <x-table-heading class="w-32">Students</x-table-heading>
                <x-table-heading class="w-24">Actions</x-table-heading>
            </x-table-row>
        </x-table-header>
        <tbody class="bg-white divide-y divide-gray-200">
            {{-- Existing Classrooms --}}
            @foreach ($workshop->classrooms as $index => $classroom)
                <x-table-row>
                    <x-table-data>
                        <input type="hidden" name="classroomIds[]" value="{{ $classroom->id }}">
                        <x-text-input
                            type="text"
                            name="classroomNames[]"
                            class="block w-full @error('classroomNames.'.$index) border-red-500 @enderror"
                            value="{{ old('classroomNames.'.$index, $classroom->name) }}"
                        />
                        @error('classroomNames.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data class="text-center">
                        <span class="text-sm text-gray-600">{{ $classroom->students()->count() }}</span>
                    </x-table-data>
                    <x-table-data class="text-center">
                        <button type="button"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-classroom-deletion-{{ $classroom->id }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                                aria-label="Delete classroom {{ $classroom->name }}">
                            Delete
                        </button>
                    </x-table-data>
                </x-table-row>
            @endforeach

            {{-- New Classroom Rows (Dynamic with Alpine.js) --}}
            <template x-for="i in newClassroomRows" :key="i">
                <x-table-row class="bg-gray-50/50">
                    <x-table-data>
                        <x-text-input
                            type="text"
                            name="newClassroomNames[]"
                            class="block w-full"
                            placeholder="New classroom name"
                        />
                    </x-table-data>
                    <x-table-data class="text-center">
                        <span class="text-sm text-gray-400">-</span>
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
        <x-secondary-button type="button" @click="newClassroomRows += 3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add 3 More Rows
        </x-secondary-button>
    </div>
</div>
