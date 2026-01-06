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
<div x-show="successMessage" x-cloak class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <span x-text="successMessage"></span>
</div>
<div x-show="errorMessage" x-cloak class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <span x-text="errorMessage"></span>
</div>

{{-- Action bar --}}
<div class="mb-6 flex justify-between items-center">
    <div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($workshop->assignment_status === 'generated') bg-green-100 text-green-800
            @elseif($workshop->assignment_status === 'manually_edited') bg-blue-100 text-blue-800
            @endif">
            @if($workshop->assignment_status === 'generated')
                Algorithm Generated
            @elseif($workshop->assignment_status === 'manually_edited')
                Manually Edited
            @endif
        </span>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('workshops.export-assignments', $workshop->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>

        <form method="POST" action="{{ route('workshops.run-algorithm', $workshop->id) }}"
              onsubmit="return confirm('This will clear all current assignments. Are you sure?')">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Re-run Algorithm
            </button>
        </form>
    </div>
</div>

{{-- Warnings section from algorithm --}}
@if(session('assignment_warnings'))
    @php
        $warnings = session('assignment_warnings');
        $errors = array_filter($warnings, fn($w) => $w['severity'] === 'error');
        $regularWarnings = array_filter($warnings, fn($w) => $w['severity'] === 'warning');
    @endphp

    @if(count($errors) > 0)
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <h4 class="text-sm font-medium text-red-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                Errors
            </h4>
            <ul class="space-y-2 text-sm text-red-700">
                @foreach($errors as $warning)
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>{{ $warning['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(count($regularWarnings) > 0)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <h4 class="text-sm font-medium text-yellow-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Warnings
            </h4>
            <ul class="space-y-2 text-sm text-yellow-700">
                @foreach($regularWarnings as $warning)
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>{{ $warning['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@elseif($unassignedStudents->count() > 0)
    {{-- Fallback for existing unassigned students display --}}
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
        <h4 class="text-sm font-medium text-yellow-800 mb-2">⚠ Warnings</h4>
        <ul class="list-disc list-inside text-sm text-yellow-700">
            <li>{{ $unassignedStudents->count() }} student(s) not assigned to any group</li>
        </ul>
    </div>
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
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            ✓ {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }}
                        </span>
                    @elseif($status === 'under')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            ⚠ {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }} (Under minimum)
                        </span>
                    @elseif($status === 'over')
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                            ✕ {{ $currentCount }}/{{ $group->minimumParticipants }}-{{ $group->maximumParticipants }} (Over maximum)
                        </span>
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

</div> {{-- Close Alpine.js wrapper --}}
