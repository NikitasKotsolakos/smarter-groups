<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form autocomplete="off" method="POST" action="{{ route("workshops.classrooms.update", [ 'workshop' => $workshop, 'classroom' => $classroom]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <x-input-label for="name" :value="__('Classroom Name')" class="required" />
                <input type="text" id="name" name="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" value="{{ $classroom->name }}" required />
            </div>
            <div class="mb-6">
                <x-input-label for="grade" :value="__('Grade')" class="required" />
                <input type="text" id="grade" name="grade" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" value="{{ $classroom->grade ?? '' }}" required />
            </div>

            <x-table>
                <x-table-header>
                    <x-table-row :hover="false">
                        <x-table-heading>Student Name</x-table-heading>
                        <x-table-heading>1st Preference</x-table-heading>
                    </x-table-row>
                </x-table-header>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($classroom->students as $student)
                    <x-table-row>
                        <x-table-data>
                            <input type="text" name="studentNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" value="{{ $student->name }}" />
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences1[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="">-- Select --</option>
                                @foreach($workshop->groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                        </x-table-data>
                    </x-table-row>
                @endforeach
                </tbody>
            </x-table>

            <div class="mt-6">
                <x-primary-button type="submit">
                    Update
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
