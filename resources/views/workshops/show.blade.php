<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8" x-data="{
        activeTab: window.location.hash ? window.location.hash.substring(1) : 'groups',
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
        }
    }">
        {{-- Alerts --}}
        @if(session('success'))
            <x-alert type="success" dismissible class="mb-4">
                {{ session('success') }}
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="error" class="mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        {{-- Workshop Header with Name and Actions --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Workshop Name (part of main update form) --}}
                <div class="flex-1">
                    <x-input-label for="name" :value="__('Workshop Name')" class="sr-only" />
                    <x-text-input
                        type="text"
                        id="name"
                        name="name"
                        form="workshop-update-form"
                        class="text-xl font-semibold w-full @error('name') border-red-500 @enderror"
                        value="{{ old('name', $workshop->name) }}"
                        required
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                {{-- Workshop Actions --}}
                <div class="flex items-center gap-3">
                    {{-- CSV Import (separate form) --}}
                    <form method="POST" action="{{ route('workshops.import', $workshop->id) }}" enctype="multipart/form-data" class="inline-flex" onsubmit="return confirmImport(event)">
                        @csrf
                        <label class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                            <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="handleFileSelect(this)">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import CSV
                        </label>
                    </form>

                    {{-- Delete Workshop Button --}}
                    <x-danger-button
                        type="button"
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-workshop-deletion')"
                        class="!px-3"
                        aria-label="Delete workshop"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </x-danger-button>
                </div>
            </div>
        </div>

        {{-- CSV Import Script --}}
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
                                  '- {{ $workshop->groups->count() }} groups\n' +
                                  '- {{ $workshop->classrooms->count() }} classrooms\n' +
                                  '- {{ $workshop->students->count() }} students\n' +
                                  '- All group preferences and assignments\n\n' +
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

        {{-- Main Workshop Update Form --}}
        <form id="workshop-update-form" autocomplete="off" method="POST" action="{{ route('workshops.update', $workshop->id) }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" role="tablist">
                    <button type="button"
                            role="tab"
                            :aria-selected="activeTab === 'groups'"
                            @click="setTab('groups')"
                            :class="activeTab === 'groups' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Groups ({{ $workshop->groups->count() }})
                    </button>
                    <button type="button"
                            role="tab"
                            :aria-selected="activeTab === 'classrooms'"
                            @click="setTab('classrooms')"
                            :class="activeTab === 'classrooms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Classrooms ({{ $workshop->classrooms->count() }})
                    </button>
                    <button type="button"
                            role="tab"
                            :aria-selected="activeTab === 'students'"
                            @click="setTab('students')"
                            :class="activeTab === 'students' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Students ({{ $workshop->students->count() }})
                    </button>
                    <button type="button"
                            role="tab"
                            :aria-selected="activeTab === 'assignments'"
                            @click="setTab('assignments')"
                            :class="activeTab === 'assignments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Assignments
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div>
                {{-- Groups Tab --}}
                <div x-show="activeTab === 'groups'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     role="tabpanel">
                    @include('workshops.partials.groups-tab')
                </div>

                {{-- Classrooms Tab --}}
                <div x-show="activeTab === 'classrooms'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     role="tabpanel">
                    @include('workshops.partials.classrooms-tab')
                </div>

                {{-- Students Tab --}}
                <div x-show="activeTab === 'students'"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     role="tabpanel">
                    @include('workshops.partials.students-tab')
                </div>
            </div>
        </form>

        {{-- Assignments Tab (outside main form - has its own forms) --}}
        <div x-show="activeTab === 'assignments'"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             role="tabpanel">
            @if($workshop->hasAssignments())
                @include('workshops.partials.assignments-display')
            @else
                @include('workshops.partials.assignments-empty-state')
            @endif
        </div>
    </div>

    {{-- All Delete Modals - OUTSIDE the main container to avoid form nesting issues --}}

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
