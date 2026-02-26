<?php

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['middleware' => ['auth', 'verified', ]], function () {
    Route::resource('workshops', WorkshopController::class)
        ->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::post('workshops/{workshop}/import', [WorkshopController::class, 'import'])->name('workshops.import');
    Route::post('workshops/{workshop}/run-algorithm', [WorkshopController::class, 'runAssignmentAlgorithm'])->name('workshops.run-algorithm');
    Route::get('workshops/{workshop}/export-assignments', [WorkshopController::class, 'exportAssignments'])->name('workshops.export-assignments');
    Route::put('workshops/{workshop}/students/{student}/assignment', [WorkshopController::class, 'updateStudentAssignment'])->name('workshops.update-student-assignment');
    Route::resource('workshops.classrooms', ClassroomController::class)
        ->only(['create', 'store', 'show', 'update', 'destroy']);

    // Delete routes for groups, students, and clear assignments
    Route::delete('workshops/{workshop}/groups/{group}', [GroupController::class, 'destroy'])
        ->name('workshops.groups.destroy');
    Route::delete('workshops/{workshop}/students/{student}', [StudentController::class, 'destroy'])
        ->name('workshops.students.destroy');
    Route::delete('workshops/{workshop}/clear-assignments', [WorkshopController::class, 'clearAssignments'])
        ->name('workshops.clear-assignments');

});


require __DIR__.'/auth.php';
