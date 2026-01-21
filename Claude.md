# Group Splitter - Laravel Application

## Project Overview

**Group Splitter** is a web application designed to help teachers/organizers assign participants (typically students) to groups within workshops based on their preferences and constraints.

### Main Purpose
- Teachers create workshops with multiple groups (projects/activities)
- Participants express their preferences for which group they want to join (ranking 2-3 options)
- An algorithm assigns participants to groups optimally based on preferences and constraints
- Teachers can manually adjust the assignments after the initial algorithm run
- The system provides a "good enough" starting point for manual iteration, not a perfect solution

### Key Use Case
A school runs workshops around different projects that mix students from different classrooms. Each student can only participate in one workshop group, but can express multiple preferences.

## Domain Model & Terminology

### Hierarchy
```
Workshop (Event/Session)
  └── Groups (Projects/Activities within the workshop)
      └── Students (Participants assigned to groups)
```

### Core Entities

#### Workshop
- **Purpose**: The overall event or session
- **Attributes**:
  - `name`: Name of the workshop
- **Relationships**:
  - Has many Groups
  - Has many Classrooms (through `workshop_classrooms` pivot)

#### Group
- **Purpose**: A specific project/activity within a workshop that students can join
- **Attributes**:
  - `name`: Name of the group/project
  - `minimumParticipants`: Minimum number of students required
  - `maximumParticipants`: Maximum number of students allowed
  - `priorityGroup`: Priority level for filling this group (higher priority = filled first, useful for less popular groups)
  - `workshop_id`: Foreign key to Workshop
- **Relationships**:
  - Belongs to a Workshop
  - Has many Students (through `groups_students` pivot)
  - Has many GroupPreferences

#### Student
- **Purpose**: A participant who will be assigned to a group
- **Attributes**:
  - `name`: Student's name
  - `classroom_id`: Foreign key to Classroom
- **Relationships**:
  - Belongs to a Classroom
  - Has many GroupPreferences (their ranked preferences)
  - Belongs to many Groups (through `groups_students` pivot - final assignments)

#### Classroom
- **Purpose**: Organizational unit for students (e.g., class 5A, 5B, etc.)
- **Attributes**:
  - `name`: Name of the classroom
- **Relationships**:
  - Has many Students
  - Belongs to many Workshops (through `workshop_classrooms` pivot)
- **Note**: May play a role in the algorithm if teachers want to "mix" students from different classrooms

#### GroupPreferences
- **Purpose**: Records a student's preference for a specific group
- **Attributes**:
  - `student_id`: Foreign key to Student
  - `group_id`: Foreign key to Group
  - `rank` or `preference_order`: (TO BE ADDED) Indicates preference ranking
- **Preference Ranking System** (flexible):
  - Students can select 2-3 groups as preferences, though they'll only be assigned to one
  - **Ranking is optional and flexible**:
    - Students may rank preferences (1st choice, 2nd choice, 3rd choice)
    - OR students may select groups without ranking (all preferences equal)
    - OR multiple preferences may have the same rank
  - **Rationale for unranked preferences**:
    - Avoids most popular projects being overwhelmed by "first choice" selections
    - Prevents students from feeling bad if they don't get their "first choice"
    - Teachers can choose whether to allow ranking based on their context
  - **Implementation**: Details on how to handle ranked vs unranked preferences TBD

#### User
- **Purpose**: Teacher/administrator who manages the system
- **Note**: Students don't currently log in to the system

## Database Structure

### Tables
- `workshops` - Workshop events
- `groups` - Groups/projects within workshops
- `students` - Student participants
- `classrooms` - Classroom organizational units
- `group_preferences` - Student preferences for groups
- `workshop_classrooms` - Pivot: Which classrooms participate in which workshops
- `groups_students` - Pivot: Final assignments of students to groups
- `users` - Teachers/administrators

### Key Relationships (Pivot Tables)
- **workshop_classrooms**: Many-to-many between Workshops and Classrooms
- **groups_students**: Many-to-many between Groups and Students (final assignments)

## Current Implementation Status

### Completed ✓
- Basic Laravel 11 setup with PHP 8.4
- All models created:
  - Workshop, Group, Student, Classroom, GroupPreferences, User
- All migrations run successfully
- Basic CRUD controllers exist:
  - WorkshopController
  - GroupController
  - StudentController
  - ClassroomController
  - GroupPreferencesController
  - ProfileController
- Laravel Breeze installed for authentication
  - User registration functional
  - User login/logout functional
- Database seeder functional
  - Creates default admin user (admin@admin.com / admin123)
  - Run with: `php artisan db:seed`
- Frontend: Vite + Tailwind CSS + Alpine.js
- **Workshop Management**:
  - Users can create workshops with groups and their parameters
  - Users can view workshops
  - Groups can be created with min/max participants and priority settings

### Not Yet Implemented ✗
- **Assignment algorithm** (main feature - core functionality)
- Student management interface (add/import students to workshops)
- Preference collection interface (students ranking their group choices)
- Manual adjustment interface (teachers tweaking algorithm results)
- Classroom management and assignment to workshops
- Classroom mixing preferences in algorithm
- Export/reporting features
- Most business logic beyond basic CRUD

## Algorithm Requirements

### Reference Implementation
- Existing Java implementation at: `/home/nikitas/programming/java/project-group-splitter-java`
- Currently works with CSV input
- Algorithm logic will need to be ported to PHP/Laravel

### Algorithm Features Needed
1. **Preference Matching**: Assign students to their preferred groups when possible
   - Must handle both ranked and unranked preferences
   - Ranked: 1st choice, 2nd choice, 3rd choice
   - Unranked: All preferences treated equally (to avoid popular group overload and student disappointment)
   - Mixed: Some preferences may share the same rank
2. **Capacity Constraints**: Respect min/max participants for each group
3. **Priority Groups**: Fill higher-priority groups first (useful for less popular groups)
4. **Classroom Mixing** (optional feature): Try to mix students from different classrooms
5. **Optimization Goal**: Maximize overall satisfaction while respecting constraints
6. **Output**: A "good enough" initial assignment that teachers can manually refine

### Algorithm Inputs
- List of students with their group preferences (may be ranked, unranked, or mixed ranking)
- List of groups with min/max capacity and priority levels
- Optional: classroom mixing preference flag
- Optional: whether preferences are ranked or unranked for this workshop

### Algorithm Output
- Assignment of each student to exactly one group
- Should respect all hard constraints (min/max capacity)
- Should optimize for preferences and priorities

## Technical Stack

### Backend
- **Framework**: Laravel 11.47.0
- **PHP Version**: 8.4.* (was initially 8.5, downgraded for stability)
- **Database**: SQLite (development)
- **Authentication**: Laravel Breeze

### Frontend
- **Build Tool**: Vite 5.0
- **CSS Framework**: Tailwind CSS 3.1
- **JavaScript Framework**: Alpine.js 3.4
- **HTTP Client**: Axios 1.7.4

### Development Tools
- **Code Style**: Laravel Pint 1.26
- **Testing**: Pest 3.8.4 with Laravel plugin
- **Local Development**: Laravel Sail (optional)

## File Locations

### Models
- `app/Models/Workshop.php`
- `app/Models/Group.php`
- `app/Models/Student.php`
- `app/Models/Classroom.php`
- `app/Models/GroupPreferences.php`
- `app/Models/User.php`

### Controllers
- `app/Http/Controllers/WorkshopController.php`
- `app/Http/Controllers/GroupController.php`
- `app/Http/Controllers/StudentController.php`
- `app/Http/Controllers/ClassroomController.php`
- `app/Http/Controllers/GroupPreferencesController.php`
- `app/Http/Controllers/ProfileController.php`

### Migrations
- `database/migrations/2024_09_07_065101_create_workshops_table.php`
- `database/migrations/2024_09_07_065151_create_groups_table.php`
- `database/migrations/2024_09_07_065134_create_students_table.php`
- `database/migrations/2024_09_07_065128_create_classrooms_table.php`
- `database/migrations/2024_09_07_070301_create_group_preferences_table.php`
- `database/migrations/2024_09_07_092238_create_workshop_classrooms_table.php`
- `database/migrations/2024_09_07_092507_create_groups_students_table.php`

## Known Issues / TODOs

### Database Schema Issues
1. **GroupPreferences missing rank field**: Need to add a `rank` or `preference_order` field
   - Should be nullable or have default value to support unranked preferences
   - When null or all same value: preferences are equal (unranked mode)
   - When different values: preferences are ranked (1st, 2nd, 3rd choice)
2. **Consider adding fields**:
   - `allow_ranking` boolean in `workshops` table to control if students can rank preferences for this workshop
   - `assigned_at` timestamp in `groups_students` to track when assignment was made
   - `manually_edited` boolean in `groups_students` to track teacher adjustments
   - `status` field in workshops to track if it's draft/active/completed

### Model Relationship Issues
1. Workshop model doesn't define relationship to Classrooms
2. Group model doesn't define relationship to Students
3. Student model relationships not visible yet
4. Classroom model relationships not visible yet

## Future Considerations

### Terminology
- Current naming (Workshop → Groups → Students) is intentionally generic
- Could be renamed in the future to be more specific (e.g., Groups → Projects)
- Keeping it generic allows the system to be used for contexts beyond student/project scenarios

### Features to Consider
- **Multi-workshop support**: Can a student participate in multiple workshops? (probably yes, but only one group per workshop)
- **Time slots**: Do workshops happen at specific times that might conflict?
- **Teacher preferences**: Should certain groups have specific teacher assignments?
- **Export/Import**: CSV export of assignments, bulk import of students
- **History**: Track changes to assignments over time
- **Notifications**: Email students their assignments

## Development Setup

### Running the Application
```bash
# Start Laravel development server
php artisan serve

# Start Vite dev server (in separate terminal)
npm run dev

# Access at: http://localhost:8000
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Seed database (creates admin user)
php artisan db:seed
```

### Testing & Demo Credentials
**Default Admin User** (created by seeder):
- Email: `admin@admin.com`
- Password: `admin123`

**Testing Workflow**:
1. Run `php artisan db:seed` to create the admin user
2. Login at `/login` with the credentials above
3. Create a workshop from the dashboard
4. Add groups to the workshop with their parameters (min/max participants, priority)
5. View the workshop and its groups

### Environment
- `.env` file configured for SQLite
- `APP_KEY` generated
- Database file: `database/database.sqlite`

## Notes for AI Assistant

### When Working on This Project
1. **Respect the generic terminology**: Don't rename Groups to Projects without explicit user request
2. **Algorithm is critical**: The assignment algorithm is the core feature - needs careful implementation
3. **Reference Java code**: When implementing the algorithm, check `/home/nikitas/programming/java/project-group-splitter-java`
4. **User-friendly adjustments**: The UI for manual adjustments is important - teachers need to easily move students between groups
5. **Database schema may need updates**: GroupPreferences needs a rank field at minimum

### Conventions
- Using Laravel 11 conventions
- Eloquent ORM for database operations
- RESTful controller methods
- Blade templates for views (with Alpine.js for interactivity)
- Following Laravel best practices

### Testing Approach
- Pest for testing
- Focus on testing the algorithm thoroughly
- Test edge cases: groups at min/max capacity, all students prefer same group, etc.
