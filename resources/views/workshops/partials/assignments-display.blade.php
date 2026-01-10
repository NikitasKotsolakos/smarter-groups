{{-- Alpine.js data wrapper for AJAX functionality --}}
<div x-data="{
    successMessage: '',
    errorMessage: '',
    async updateAssignment(studentId, groupId) {
        try {
            const response = await fetch('{{ route('workshops.update-student-assignment', [$workshop->id, ':studentId']) }}'.replace(':studentId', studentId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ group_id: groupId })
            });

            const data = await response.json();

            if (response.ok) {
                this.successMessage = data.message || 'Student assignment updated successfully';
                this.errorMessage = '';
                // Reload page to show updated assignments
                setTimeout(() => window.location.reload(), 500);
            } else {
                this.errorMessage = data.message || 'Failed to update assignment';
                this.successMessage = '';
            }
        } catch (error) {
            this.errorMessage = 'An error occurred while updating the assignment';
            this.successMessage = '';
        }
    }
}">

{{-- Success/Error messages --}}
<template x-if="successMessage">
    <x-alert type="success" class="mb-4">
        <span x-text="successMessage"></span>
    </x-alert>
</template>
<template x-if="errorMessage">
    <x-alert type="error" class="mb-4">
        <span x-text="errorMessage"></span>
    </x-alert>
</template>

{{-- Action bar --}}
<div class="mb-6 flex justify-between items-center">
    <div>
        @if($workshop->assignment_status === 'generated')
            <x-badge variant="success">Algorithm Generated</x-badge>
        @elseif($workshop->assignment_status === 'manually_edited')
            <x-badge variant="info">Manually Edited</x-badge>
        @endif
    </div>

    <div class="flex gap-2">
        <a href="{{ route('workshops.export-assignments', [$workshop->id, 'format' => 'csv']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>

        <a href="{{ route('workshops.export-assignments', [$workshop->id, 'format' => 'xlsx']) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Excel
        </a>

        <form method="POST" action="{{ route('workshops.run-algorithm', $workshop->id) }}"
              onsubmit="return confirm('This will clear all current assignments. Are you sure?')">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Re-run Algorithm
            </button>
        </form>

        <button type="button"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-clear-assignments')"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Clear All Assignments
        </button>
    </div>
</div>

{{-- Warnings section from algorithm --}}
@if(session('assignment_warnings'))
    @php
        $warnings = session('assignment_warnings');
        $algorithmErrors = array_filter($warnings, fn($w) => $w['severity'] === 'error');
        $regularWarnings = array_filter($warnings, fn($w) => $w['severity'] === 'warning');
    @endphp

    @if(count($algorithmErrors) > 0)
        <x-alert type="error" class="mb-6">
            <h4 class="font-medium mb-2">Errors</h4>
            <ul class="space-y-1">
                @foreach($algorithmErrors as $warning)
                    <li class="flex items-start">
                        <span class="mr-2">-</span>
                        <span>{{ $warning['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @if(count($regularWarnings) > 0)
        <x-alert type="warning" class="mb-6">
            <h4 class="font-medium mb-2">Warnings</h4>
            <ul class="space-y-1">
                @foreach($regularWarnings as $warning)
                    <li class="flex items-start">
                        <span class="mr-2">-</span>
                        <span>{{ $warning['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif
@elseif($unassignedStudents->count() > 0)
    {{-- Fallback for existing unassigned students display --}}
    <x-alert type="warning" class="mb-6">
        <strong>Warnings:</strong> {{ $unassignedStudents->count() }} student(s) not assigned to any group
    </x-alert>
@endif

{{-- Groups with assignments --}}
<div class="space-y-6">
    @foreach($workshop->groups as $group)
        @php
            $status = $group->getCapacityStatus();
            $currentCount = $group->getCurrentCount();
        @endphp

        <div class="border rounded-lg overflow-hidden">
            {{-- Group header --}}
            <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <h3 class="font-semibold text-gray-900">{{ $group->name }}</h3>

                    {{-- Status indicator --}}
                    @if($status === 'ok')
                        <x-badge variant="success" size="sm">
                            {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }}
                        </x-badge>
                    @elseif($status === 'under')
                        <x-badge variant="warning" size="sm">
                            {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }} (Under minimum)
                        </x-badge>
                    @elseif($status === 'over')
                        <x-badge variant="error" size="sm">
                            {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }} (Over maximum)
                        </x-badge>
                    @endif
                </div>
            </div>

            {{-- Students list --}}
            @if($group->students->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Classroom</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Move to</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($group->students as $student)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $student->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $student->classroom->name }}</td>
                                <td class="px-4 py-2">
                                    <select @change="updateAssignment({{ $student->id }}, $event.target.value)" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" autocomplete="off">
                                        <option value="">-- Select Group --</option>
                                        @foreach($workshop->groups as $targetGroup)
                                            <option value="{{ $targetGroup->id }}" {{ $targetGroup->id == $group->id ? 'selected' : '' }}>
                                                {{ $targetGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-4 py-6 text-center text-sm text-gray-500">
                    No students assigned to this group
                </div>
            @endif
        </div>
    @endforeach
</div>

{{-- Unassigned students section --}}
@if($unassignedStudents->count() > 0)
    <div class="mt-6 border rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-red-50 border-b">
            <h3 class="font-semibold text-gray-900">Unassigned Students ({{ $unassignedStudents->count() }})</h3>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Classroom</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Assign to</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($unassignedStudents as $student)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $student->name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $student->classroom->name }}</td>
                        <td class="px-4 py-2">
                            <select @change="updateAssignment({{ $student->id }}, $event.target.value)" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" autocomplete="off">
                                <option value="">-- Select Group --</option>
                                @foreach($workshop->groups as $targetGroup)
                                    <option value="{{ $targetGroup->id }}">{{ $targetGroup->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Clear Assignments Modal --}}
<x-modal name="confirm-clear-assignments" focusable>
    <form method="post" action="{{ route('workshops.clear-assignments', $workshop->id) }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-medium text-gray-900">
            Clear all student assignments?
        </h2>

        <p class="mt-3 text-sm text-gray-600">
            This will remove all students from their assigned groups. Students and groups will not be deleted.
        </p>

        <p class="mt-2 text-sm text-gray-600">
            You can re-run the algorithm or manually assign students afterwards.
        </p>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>

            <x-danger-button>
                Clear Assignments
            </x-danger-button>
        </div>
    </form>
</x-modal>

</div> {{-- Close Alpine.js wrapper --}}
