<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Group;
use App\Models\GroupPreferences;
use App\Models\Student;
use App\Models\User;
use App\Models\Workshop;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin123'),
        ]);

        // Create first workshop
        $workshop1 = Workshop::create([
            'name' => 'Spring Project Workshop',
            'user_id' => $admin->id,
        ]);

        $robotics = Group::create(['workshop_id' => $workshop1->id, 'name' => 'Robotics', 'minimumParticipants' => 8, 'maximumParticipants' => 15, 'priorityGroup' => 1]);
        $artDesign = Group::create(['workshop_id' => $workshop1->id, 'name' => 'Art & Design', 'minimumParticipants' => 10, 'maximumParticipants' => 20, 'priorityGroup' => 2]);
        $music = Group::create(['workshop_id' => $workshop1->id, 'name' => 'Music Production', 'minimumParticipants' => 6, 'maximumParticipants' => 12, 'priorityGroup' => 3]);
        $coding = Group::create(['workshop_id' => $workshop1->id, 'name' => 'Coding & Tech', 'minimumParticipants' => 10, 'maximumParticipants' => 18, 'priorityGroup' => 1]);

        // Create classrooms for workshop 1
        $class5A = Classroom::create(['workshop_id' => $workshop1->id, 'name' => '5A']);
        $class5B = Classroom::create(['workshop_id' => $workshop1->id, 'name' => '5B']);
        $class5C = Classroom::create(['workshop_id' => $workshop1->id, 'name' => '5C']);

        // Create students for workshop 1 - Class 5A (15 students)
        $students5A = [
            'Emma Wilson', 'Liam Johnson', 'Olivia Brown', 'Noah Davis', 'Ava Martinez',
            'Elijah Anderson', 'Sophia Taylor', 'James Thomas', 'Isabella Moore', 'Benjamin Jackson',
            'Mia White', 'Lucas Harris', 'Charlotte Clark', 'Henry Lewis', 'Amelia Walker'
        ];

        foreach ($students5A as $name) {
            $student = Student::create(['name' => $name, 'classroom_id' => $class5A->id]);
            // Assign random preferences
            $preferences = $this->generateRandomPreferences([$robotics->id, $artDesign->id, $music->id, $coding->id]);
            foreach ($preferences as $rank => $groupId) {
                GroupPreferences::create(['student_id' => $student->id, 'group_id' => $groupId, 'rank' => $rank]);
            }
        }

        // Create students for workshop 1 - Class 5B (16 students)
        $students5B = [
            'Alexander Young', 'Harper King', 'Michael Wright', 'Evelyn Lopez', 'Daniel Hill',
            'Abigail Scott', 'Matthew Green', 'Emily Adams', 'Joseph Baker', 'Ella Nelson',
            'David Carter', 'Scarlett Mitchell', 'Christopher Perez', 'Grace Roberts', 'Andrew Turner',
            'Chloe Phillips'
        ];

        foreach ($students5B as $name) {
            $student = Student::create(['name' => $name, 'classroom_id' => $class5B->id]);
            $preferences = $this->generateRandomPreferences([$robotics->id, $artDesign->id, $music->id, $coding->id]);
            foreach ($preferences as $rank => $groupId) {
                GroupPreferences::create(['student_id' => $student->id, 'group_id' => $groupId, 'rank' => $rank]);
            }
        }

        // Create students for workshop 1 - Class 5C (14 students)
        $students5C = [
            'Samuel Campbell', 'Victoria Parker', 'Jack Evans', 'Zoe Edwards', 'Owen Collins',
            'Lily Stewart', 'Luke Sanchez', 'Aria Morris', 'Ryan Rogers', 'Layla Reed',
            'Nathan Cook', 'Penelope Morgan', 'Isaac Bell', 'Nora Murphy'
        ];

        foreach ($students5C as $name) {
            $student = Student::create(['name' => $name, 'classroom_id' => $class5C->id]);
            $preferences = $this->generateRandomPreferences([$robotics->id, $artDesign->id, $music->id, $coding->id]);
            foreach ($preferences as $rank => $groupId) {
                GroupPreferences::create(['student_id' => $student->id, 'group_id' => $groupId, 'rank' => $rank]);
            }
        }

        // Seed assignments for workshop 1 (as if algorithm had run)
        $this->seedWorkshop1Assignments($workshop1, $admin, [$robotics, $artDesign, $music, $coding]);

        // Create second workshop
        $workshop2 = Workshop::create([
            'name' => 'Summer Activities 2026',
            'user_id' => $admin->id,
        ]);

        $sports = Group::create(['workshop_id' => $workshop2->id, 'name' => 'Sports & Athletics', 'minimumParticipants' => 12, 'maximumParticipants' => 20, 'priorityGroup' => 2]);
        $drama = Group::create(['workshop_id' => $workshop2->id, 'name' => 'Drama & Theater', 'minimumParticipants' => 8, 'maximumParticipants' => 15, 'priorityGroup' => 1]);
        $science = Group::create(['workshop_id' => $workshop2->id, 'name' => 'Science Lab', 'minimumParticipants' => 10, 'maximumParticipants' => 16, 'priorityGroup' => 3]);

        // Create classrooms for workshop 2
        $class6A = Classroom::create(['workshop_id' => $workshop2->id, 'name' => '6A']);
        $class6B = Classroom::create(['workshop_id' => $workshop2->id, 'name' => '6B']);

        // Create students for workshop 2 - Class 6A (12 students)
        $students6A = [
            'Sofia Rodriguez', 'Jackson Lee', 'Avery Gonzalez', 'Sebastian Hernandez', 'Ella Martinez',
            'Aiden Wilson', 'Madison Anderson', 'Mason Thomas', 'Scarlett Taylor', 'Logan Moore',
            'Hannah Jackson', 'Ethan White'
        ];

        foreach ($students6A as $name) {
            $student = Student::create(['name' => $name, 'classroom_id' => $class6A->id]);
            $preferences = $this->generateRandomPreferences([$sports->id, $drama->id, $science->id]);
            foreach ($preferences as $rank => $groupId) {
                GroupPreferences::create(['student_id' => $student->id, 'group_id' => $groupId, 'rank' => $rank]);
            }
        }

        // Create students for workshop 2 - Class 6B (13 students)
        $students6B = [
            'Carter Harris', 'Aubrey Martin', 'Wyatt Thompson', 'Addison Garcia', 'Grayson Clark',
            'Lillian Rodriguez', 'Julian Lewis', 'Zoey Walker', 'Leo Hall', 'Natalie Allen',
            'Caleb Young', 'Hazel King', 'Aaron Wright'
        ];

        foreach ($students6B as $name) {
            $student = Student::create(['name' => $name, 'classroom_id' => $class6B->id]);
            $preferences = $this->generateRandomPreferences([$sports->id, $drama->id, $science->id]);
            foreach ($preferences as $rank => $groupId) {
                GroupPreferences::create(['student_id' => $student->id, 'group_id' => $groupId, 'rank' => $rank]);
            }
        }
    }

    /**
     * Generate random preferences for a student.
     * Returns an array with rank => group_id.
     * Some students get 3 preferences, some get 2, some get 1.
     */
    private function generateRandomPreferences(array $groupIds): array
    {
        $preferences = [];
        shuffle($groupIds); // Randomize order

        // Randomly decide how many preferences (1-3)
        $numPreferences = rand(1, 3);

        for ($i = 0; $i < $numPreferences; $i++) {
            $preferences[$i + 1] = $groupIds[$i]; // rank is 1-indexed
        }

        return $preferences;
    }

    /**
     * Seed assignments for workshop 1 (simulate algorithm results).
     * Distributes 45 students across 4 groups in a balanced way.
     */
    private function seedWorkshop1Assignments(Workshop $workshop, User $admin, array $groups): void
    {
        [$robotics, $artDesign, $music, $coding] = $groups;

        // Get all students for this workshop
        $allStudents = $workshop->students()->get();

        // Distribute students round-robin across groups
        $groupsArray = [$robotics, $artDesign, $music, $coding];
        $groupIndex = 0;

        foreach ($allStudents as $student) {
            $group = $groupsArray[$groupIndex];

            // Attach student to group with metadata
            $group->students()->attach($student->id, [
                'assignment_method' => 'algorithm',
                'assigned_at' => now(),
                'assigned_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Move to next group (round-robin)
            $groupIndex = ($groupIndex + 1) % count($groupsArray);
        }

        // Update workshop status
        $workshop->update(['assignment_status' => 'generated']);
    }
}
