<?php

namespace Database\Seeders;

use App\Models\Group;
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

        Group::create(['workshop_id' => $workshop1->id, 'name' => 'Robotics', 'minimumParticipants' => 8, 'maximumParticipants' => 15, 'priorityGroup' => 1]);
        Group::create(['workshop_id' => $workshop1->id, 'name' => 'Art & Design', 'minimumParticipants' => 10, 'maximumParticipants' => 20, 'priorityGroup' => 2]);
        Group::create(['workshop_id' => $workshop1->id, 'name' => 'Music Production', 'minimumParticipants' => 6, 'maximumParticipants' => 12, 'priorityGroup' => 3]);
        Group::create(['workshop_id' => $workshop1->id, 'name' => 'Coding & Tech', 'minimumParticipants' => 10, 'maximumParticipants' => 18, 'priorityGroup' => 1]);

        // Create second workshop
        $workshop2 = Workshop::create([
            'name' => 'Summer Activities 2026',
            'user_id' => $admin->id,
        ]);

        Group::create(['workshop_id' => $workshop2->id, 'name' => 'Sports & Athletics', 'minimumParticipants' => 12, 'maximumParticipants' => 20, 'priorityGroup' => 2]);
        Group::create(['workshop_id' => $workshop2->id, 'name' => 'Drama & Theater', 'minimumParticipants' => 8, 'maximumParticipants' => 15, 'priorityGroup' => 1]);
        Group::create(['workshop_id' => $workshop2->id, 'name' => 'Science Lab', 'minimumParticipants' => 10, 'maximumParticipants' => 16, 'priorityGroup' => 3]);
    }
}
