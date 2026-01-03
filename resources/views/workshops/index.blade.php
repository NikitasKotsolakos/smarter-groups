<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">My Workshops</h2>
            <a href="{{ route('workshops.create') }}" class="btn btn-primary">
                Create New Workshop
            </a>
        </div>

        @if($workshops->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500 mb-4">You haven't created any workshops yet.</p>
                <a href="{{ route('workshops.create') }}" class="btn btn-primary">
                    Create Your First Workshop
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Workshop Name</th>
                            <th>Groups</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workshops as $workshop)
                            <tr>
                                <td>
                                    <a href="{{ route('workshops.show', $workshop->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $workshop->name }}
                                    </a>
                                </td>
                                <td>{{ $workshop->groups->count() }}</td>
                                <td>{{ $workshop->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('workshops.show', $workshop->id) }}" class="btn btn-sm btn-primary">
                                        View/Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
