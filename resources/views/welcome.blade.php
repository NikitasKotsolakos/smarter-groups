<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Smarter Groups</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="min-h-screen flex flex-col">
            <!-- Header -->
            <header class="w-full py-6 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">Smarter Groups</span>
                    </div>

                    @if (Route::has('login'))
                        <nav class="flex items-center space-x-4">
                            @auth
                                <a href="{{ route('workshops.index') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                    My Workshops
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors duration-200">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Hero Section -->
                    <div class="mb-12">
                        <h1 class="text-5xl sm:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                            Preference Based<br/>
                            <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Smarter Groups
                            </span>
                        </h1>
                        <p class="text-xl sm:text-2xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto leading-relaxed">
                            Automatically assign students to workshop groups based on their preferences using smart algorithms
                        </p>
                    </div>

                    <!-- Feature Cards -->
                    <div class="grid md:grid-cols-3 gap-6 mb-12">
                        <!-- Feature 1 -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Collect Preferences</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Students rank their top group choices, ensuring everyone's voice is heard
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Smart Algorithm</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Priority-based assignment maximizes satisfaction while respecting group constraints
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Manual Adjustments</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Fine-tune assignments with an intuitive interface after the algorithm runs
                            </p>
                        </div>
                    </div>

                    <!-- How It Works -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl mb-12">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">How It Works</h2>
                        <div class="grid md:grid-cols-4 gap-6 text-left">
                            <div class="flex flex-col">
                                <div class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mb-3">1</div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Create Workshop</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Set up groups and add students from different classrooms</p>
                            </div>
                            <div class="flex flex-col">
                                <div class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mb-3">2</div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Gather Preferences</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Students select their preferred groups with rankings</p>
                            </div>
                            <div class="flex flex-col">
                                <div class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mb-3">3</div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Run Algorithm</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Automatic assignment based on preferences and constraints</p>
                            </div>
                            <div class="flex flex-col">
                                <div class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mb-3">4</div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Review & Export</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Adjust if needed and export final assignments</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    @guest
                        <div>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Get Started Free
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No credit card required</p>
                        </div>
                    @endguest
                </div>
            </main>

            <!-- Footer -->
            <footer class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} Smarter Groups. Built with Laravel {{ Illuminate\Foundation\Application::VERSION }}</p>
            </footer>
        </div>
    </body>
</html>
