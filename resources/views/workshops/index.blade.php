<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">My Workshops</h2>
            <x-button-link href="{{ route('workshops.create') }}" variant="primary">
                Create New Workshop
            </x-button-link>
        </div>

        @if($workshops->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500 mb-4">You haven't created any workshops yet.</p>
                <x-button-link href="{{ route('workshops.create') }}" variant="primary">
                    Create Your First Workshop
                </x-button-link>
            </div>
        @else
            <x-table>
                <x-table-header>
                    <x-table-row :hover="false">
                        <x-table-heading>Workshop Name</x-table-heading>
                        <x-table-heading>Groups</x-table-heading>
                        <x-table-heading>Created</x-table-heading>
                        <x-table-heading>Actions</x-table-heading>
                    </x-table-row>
                </x-table-header>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($workshops as $workshop)
                        <x-table-row>
                            <x-table-data>
                                <a href="{{ route('workshops.show', $workshop->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $workshop->name }}
                                </a>
                            </x-table-data>
                            <x-table-data>{{ $workshop->groups->count() }}</x-table-data>
                            <x-table-data>{{ $workshop->created_at->format('M d, Y') }}</x-table-data>
                            <x-table-data>
                                <x-button-link href="{{ route('workshops.show', $workshop->id) }}" variant="primary" class="!py-1 !text-[11px]">
                                    View/Edit
                                </x-button-link>
                            </x-table-data>
                        </x-table-row>
                    @endforeach
                </tbody>
            </x-table>
        @endif
    </div>
</x-app-layout>
