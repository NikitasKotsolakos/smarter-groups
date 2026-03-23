<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form autocomplete="off" method="POST" action="{{ route("workshops.classrooms.store", $workshop->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <x-input-label for="name" :value="__('Classroom Name')" class="required" />
                <input type="text" id="name" name="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required />
            </div>
            <div class="mb-6">
                <x-input-label for="grade" :value="__('Grade')" class="required" />
                <input type="text" id="grade" name="grade" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required />
            </div>

            <x-table>
                <x-table-header>
                    <x-table-row :hover="false">
                        <x-table-heading>Student Name</x-table-heading>
                        <x-table-heading>1st Preference</x-table-heading>
                        <x-table-heading>2nd Preference</x-table-heading>
                        <x-table-heading>3rd Preference</x-table-heading>
                        <x-table-heading>4th Preference</x-table-heading>
                        <x-table-heading>5th Preference</x-table-heading>
                    </x-table-row>
                </x-table-header>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach (range(0, 30) as $index)
                    <x-table-row>
                        <x-table-data>
                            <input type="text" name="studentNames[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" />
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences1[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="" selected></option>
                                @foreach($groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences2[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="" selected></option>
                                @foreach($groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences3[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="" selected></option>
                                @foreach($groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences4[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="" selected></option>
                                @foreach($groups as $group)
                                    <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                        </x-table-data>
                        <x-table-data>
                            <select name="preferences5[]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <option value="" selected></option>
                                @foreach($groups as $group)
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
                    Save
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
