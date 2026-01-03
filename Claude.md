# Group Splitter - Laravel Application

> **Related Documentation**: See [IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md) for current status, algorithm requirements, and development roadmap.

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
  - `user_id`: Foreign key to User (who created the workshop)
- **Relationships**:
  - Belongs to User (creator/owner)
  - Has many Groups
  - Has many Classrooms

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
  - `workshop_id`: Foreign key to Workshop
- **Relationships**:
  - Belongs to a Workshop
  - Has many Students
- **Scope**: Classrooms are per-workshop (not shared across workshops)
- **Note**: May play a role in the algorithm if teachers want to "mix" students from different classrooms

#### GroupPreferences
- **Purpose**: Records a student's preference for a specific group
- **Attributes**:
  - `student_id`: Foreign key to Student
  - `group_id`: Foreign key to Group
  - `rank`: Nullable integer indicating preference ranking (1 = 1st choice, 2 = 2nd choice, 3 = 3rd choice)
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
- **Relationships**:
  - Has many Workshops (created/owned workshops)
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

> **For detailed implementation status, roadmap, and TODOs**: See [IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md)

### Quick Status Summary
- ✓ Basic Laravel setup complete with authentication
- ✓ Database models and migrations in place
- ✓ Workshop management: create, list, view, edit (user-scoped)
- ✓ Group management: create, edit within workshops (tab-based UI)
- ✓ Classroom management: create, edit within workshops (tab-based UI)
- ✓ Student management: create, edit with classroom assignment (tab-based UI)
- ✓ Preference collection: students can select up to 3 ranked group preferences
- ✗ Assignment algorithm (core feature) - not yet implemented
- ✗ Manual adjustment interface - not yet implemented

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

## Design Philosophy & Terminology

### Terminology Choices
- Current naming (Workshop → Groups → Students) is intentionally generic
- Could be renamed in the future to be more specific (e.g., Groups → Projects)
- Keeping it generic allows the system to be used for contexts beyond student/project scenarios
- **Important**: Don't rename Groups to Projects without explicit user request

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

**Sample Data** (created by seeder):
- 2 workshops with groups, classrooms, students, and preferences pre-populated for testing
- **Workshop 1: "Spring Project Workshop"**
  - 4 groups: Robotics, Art & Design, Music Production, Coding & Tech
  - 3 classrooms: 5A (15 students), 5B (16 students), 5C (14 students)
  - Total: 45 students with randomized group preferences
- **Workshop 2: "Summer Activities 2026"**
  - 3 groups: Sports & Athletics, Drama & Theater, Science Lab
  - 2 classrooms: 6A (12 students), 6B (13 students)
  - Total: 25 students with randomized group preferences
- Students have 1-3 ranked preferences each (randomized for testing)

**Testing Workflow**:
1. Run `php artisan migrate:fresh --seed` to reset database with sample data
2. Login at `/login` with the credentials above
3. View the workshop list at "My Workshops"
4. Click on a workshop to view/edit details
5. Create new workshops as needed

### Environment
- `.env` file configured for SQLite
- `APP_KEY` generated
- Database file: `database/database.sqlite`

## Notes for AI Assistant

### Key Reminders
1. **This is the domain knowledge file** - For implementation details, TODOs, and roadmap, always check [IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md)
2. **Respect the generic terminology**: Don't rename Groups to Projects without explicit user request
3. **Algorithm is critical**: The assignment algorithm is the core feature - reference Java implementation at `/home/nikitas/programming/java/project-group-splitter-java`
4. **Preference flexibility**: System must support both ranked and unranked preference modes
5. **User-friendly adjustments**: Teachers need to easily move students between groups after algorithm runs

### Conventions
- Laravel 11 with PHP 8.4
- Eloquent ORM for database operations
- RESTful controller methods
- Blade templates for views (with Alpine.js for interactivity)
- Pest for testing
- Following Laravel best practices
