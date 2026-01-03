<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <h2 class="text-2xl font-semibold mb-6">Create New Workshop</h2>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form autocomplete="off" method="POST" action="{{ route("workshops.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group mb-6">
                <label class="required" for="name">Workshop Name</label>
                <input class="form-control @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
                <p class="text-gray-600 text-sm mt-2">You'll be able to add groups, classrooms, and students after creating the workshop.</p>
            </div>

            <div class="form-group flex gap-3">
                <button class="btn btn-danger" type="submit">
                    Create Workshop
                </button>
                <a href="{{ route('workshops.index') }}" class="btn bg-gray-500 hover:bg-gray-600 text-white">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
