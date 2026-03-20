<div class="text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">No assignments yet</h3>
    <p class="mt-1 text-sm text-gray-500">
        Run the assignment algorithm to automatically assign students to groups.
    </p>

    @if ($workshop->groups()->count() === 0 || $workshop->students()->count() === 0)
        <x-alert type="warning" class="mt-4 text-left">
            <strong>Cannot run algorithm:</strong>
            @if ($workshop->groups()->count() === 0)
                You need to add at least one group first.
            @elseif ($workshop->students()->count() === 0)
                You need to add at least one student first.
            @endif
        </x-alert>
    @else
        <form method="POST" action="{{ route('workshops.run-algorithm', $workshop->id) }}" class="mt-6">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Run Algorithm
            </button>
        </form>
    @endif
</div>
