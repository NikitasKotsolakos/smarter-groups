{{-- Groups Tab Content --}}
{{-- Receives: $workshop --}}

<div x-data="{ newGroupRows: 3 }">
    {{-- Section Header with Update Button --}}
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Manage Groups</h3>
        <x-primary-button type="submit" x-bind:disabled="submitting">
            <span x-show="!submitting">Update Groups</span>
            <span x-show="submitting" class="flex items-center">
                <x-loading-spinner size="sm" class="mr-2" />
                Saving...
            </span>
        </x-primary-button>
    </div>

    <x-table>
        <x-table-header>
            <x-table-row :hover="false">
                <x-table-heading>Group Name</x-table-heading>
                <x-table-heading>Min Participants</x-table-heading>
                <x-table-heading>Max Participants</x-table-heading>
                <x-table-heading>Priority</x-table-heading>
                <x-table-heading class="w-24">Actions</x-table-heading>
            </x-table-row>
        </x-table-header>
        <tbody class="bg-white divide-y divide-gray-200">
            {{-- Existing Groups --}}
            @foreach ($workshop->groups as $index => $group)
                <x-table-row>
                    <x-table-data>
                        <input type="hidden" name="groupIds[]" value="{{ $group->id }}">
                        <x-text-input
                            type="text"
                            name="groupNames[]"
                            class="block w-full @error('groupNames.'.$index) border-red-500 @enderror"
                            value="{{ old('groupNames.'.$index, $group->name) }}"
                        />
                        @error('groupNames.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="minimumParticipants[]"
                            class="block w-full @error('minimumParticipants.'.$index) border-red-500 @enderror"
                            value="{{ old('minimumParticipants.'.$index, $group->minimumParticipants) }}"
                            min="0"
                        />
                        @error('minimumParticipants.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="maximumParticipants[]"
                            class="block w-full @error('maximumParticipants.'.$index) border-red-500 @enderror"
                            value="{{ old('maximumParticipants.'.$index, $group->maximumParticipants) }}"
                            min="0"
                        />
                        @error('maximumParticipants.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="priorityGroups[]"
                            class="block w-full @error('priorityGroups.'.$index) border-red-500 @enderror"
                            value="{{ old('priorityGroups.'.$index, $group->priorityGroup) }}"
                            min="1"
                        />
                        @error('priorityGroups.'.$index)
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </x-table-data>
                    <x-table-data class="text-center">
                        <button type="button"
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-group-deletion-{{ $group->id }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                                aria-label="Delete group {{ $group->name }}">
                            Delete
                        </button>
                    </x-table-data>
                </x-table-row>
            @endforeach

            {{-- New Group Rows (Dynamic with Alpine.js) --}}
            <template x-for="i in newGroupRows" :key="i">
                <x-table-row class="bg-gray-50/50">
                    <x-table-data>
                        <x-text-input
                            type="text"
                            name="newGroupNames[]"
                            class="block w-full"
                            placeholder="New group name"
                        />
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="newMinimumParticipants[]"
                            class="block w-full"
                            value="10"
                            placeholder="10"
                            min="0"
                        />
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="newMaximumParticipants[]"
                            class="block w-full"
                            value="20"
                            placeholder="20"
                            min="0"
                        />
                    </x-table-data>
                    <x-table-data>
                        <x-text-input
                            type="number"
                            name="newPriorityGroups[]"
                            class="block w-full"
                            value="1"
                            placeholder="1"
                            min="1"
                        />
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
        <x-secondary-button type="button" @click="newGroupRows += 3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add 3 More Rows
        </x-secondary-button>
    </div>
</div>
