<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <x-page-header title="Create New Workshop" />

        @if($errors->any())
            <x-alert type="error" class="mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-alert>
        @endif

        <form autocomplete="off" method="POST" action="{{ route("workshops.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <x-input-label for="name" :value="__('Workshop Name')" class="required" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full @error('name') border-red-500 @enderror" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                <p class="text-gray-600 text-sm mt-2">You'll be able to add groups, classrooms, and students after creating the workshop.</p>
            </div>

            <div class="flex gap-3">
                <x-primary-button type="submit">
                    Create Workshop
                </x-primary-button>
                <x-button-link href="{{ route('workshops.index') }}" variant="secondary">
                    Cancel
                </x-button-link>
            </div>
        </form>
    </div>
</x-app-layout>
