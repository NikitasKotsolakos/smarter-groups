# Smarter Groups - Laravel Application

## Project History

This project was originally named "Group Splitter" and the repository was called `group-splitter`. It was renamed to "Smarter Groups" (`smarter-groups`) in January 2026. You may encounter references to the old name in:
- Git history
- External documentation
- The Java reference implementation at `/home/nikitas/programming/java/project-group-splitter-java`

---

> **Related Documentation**: See [IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md) for current status, algorithm requirements, and development roadmap.

## Project Overview

**Smarter Groups** is a web application designed to help teachers/organizers assign participants (typically students) to groups within workshops based on their preferences and constraints.

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
  - `assignment_status`: Enum ('none', 'generated', 'manually_edited') tracking assignment state
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
  - `priorityGroup`: Priority level for filling this group (lower number = higher priority, filled first)
  - `max_students_from_one_classroom`: Maximum students from same classroom (nullable, defaults to maximumParticipants if null)
  - `workshop_id`: Foreign key to Workshop
- **Methods**:
  - `getPopularity()`: Returns count of students who selected this group as a preference
  - `getEffectiveMaxFromClassroom()`: Returns max_students_from_one_classroom or maximumParticipants if null
  - `getCurrentCount()`: Returns current number of assigned students
  - `getCapacityStatus()`: Returns 'ok', 'under', or 'over' based on current count vs min/max
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
  - Includes metadata: `assignment_method` (algorithm/manual), `assigned_at`, `assigned_by`

## Current Implementation Status

> **For detailed implementation status, roadmap, and TODOs**: See [IMPLEMENTATION_PLAN.md](docs/IMPLEMENTATION_PLAN.md)

### Quick Status Summary
- ✓ Basic Laravel setup complete with authentication
- ✓ Database models and migrations in place
- ✓ Workshop management: create, list, view, edit, delete (user-scoped)
- ✓ Group management: create, edit, delete within workshops (tab-based UI)
- ✓ Classroom management: create, edit, delete within workshops (tab-based UI)
- ✓ Student management: create, edit, delete with classroom assignment (tab-based UI)
- ✓ Preference collection: students can select up to 3 ranked group preferences
- ✓ CSV import: bulk import groups, classrooms, students, and preferences from CSV file
- ✓ Assignments tab: view and manage student-group assignments
- ✓ Assignment algorithm: **full priority-based greedy algorithm with dynamic adjustment**
- ✓ Manual adjustment interface: dropdown-based editing of student assignments with AJAX updates
- ✓ Delete operations: workshops, groups, classrooms, students with modal confirmations and cascade cleanup
- ✓ Clear assignments: remove all student-group assignments without deleting entities
- ✓ Warnings & validation: comprehensive error and warning display for assignment issues

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

### Services
- `app/Services/AssignmentAlgorithm/AssignmentService.php` - Main algorithm orchestrator
- `app/Services/AssignmentAlgorithm/GroupSorter.php` - Group sorting with priority and tie-breaking
- `app/Services/AssignmentAlgorithm/StudentSorter.php` - Student sorting by preference urgency
- `app/Services/AssignmentAlgorithm/ConstraintChecker.php` - Validation and constraint checking
- `app/Services/AssignmentAlgorithm/DTOs/AssignmentResult.php` - Result data transfer object

### Migrations
- `database/migrations/2024_09_07_065101_create_workshops_table.php`
- `database/migrations/2024_09_07_065151_create_groups_table.php`
- `database/migrations/2024_09_07_065134_create_students_table.php`
- `database/migrations/2024_09_07_065128_create_classrooms_table.php`
- `database/migrations/2024_09_07_070301_create_group_preferences_table.php`
- `database/migrations/2024_09_07_092238_create_workshop_classrooms_table.php`
- `database/migrations/2024_09_07_092507_create_groups_students_table.php`
- `database/migrations/2026_01_05_072312_add_classroom_mixing_to_groups_table.php`

## Design Philosophy & Terminology

### Terminology Choices
- Current naming (Workshop → Groups → Students) is intentionally generic
- Could be renamed in the future to be more specific (e.g., Groups → Projects)
- Keeping it generic allows the system to be used for contexts beyond student/project scenarios
- **Important**: Don't rename Groups to Projects without explicit user request

## CSV Import Feature

### Overview
The system supports bulk importing of workshop data via CSV files. This feature is designed to match the format used in the Java reference implementation at `/home/nikitas/programming/java/project-group-splitter-java`.

### CSV Format
- **Separator**: Semicolon (`;`)
- **Column 0**: Classroom name
- **Column 1**: Student name (required)
- **Columns 2+**: Group preferences
  - Header row contains group names
  - Data cells contain "1" to indicate a preference
  - Multiple "1"s per row are ranked in order (1st choice, 2nd choice, 3rd choice)

### Import Behavior
1. **Data Replacement**: Deletes ALL existing workshop data before importing:
   - All groups (and their preferences/assignments)
   - All classrooms (and their students, preferences, assignments)
   - Resets workshop assignment status to 'none'
   - **Warning**: Confirmation dialog shows counts of data that will be deleted
2. **Groups**: Created from header row (columns 2+) with default values:
   - Minimum participants: 8
   - Maximum participants: 15
   - Priority: 1
3. **Classrooms**: Created from unique values in column 0
4. **Students**: Created from column 1, assigned to classroom from column 0
5. **Preferences**: Created for each "1" in student's row, ranked by column order
6. **File handling**: Uploaded file is deleted immediately after processing
7. **Transaction safety**: All operations wrapped in database transaction (rollback on error)

### Access
- Available on workshop show/edit page via "Import from CSV" button
- Auto-submits on file selection with confirmation dialog
- Shows warning if workshop has existing data
- Provides success/error feedback to user

## Assignments Feature

### Overview
The Assignments tab allows teachers to run the assignment algorithm and view/edit the results. This is the core feature where students are assigned to groups based on their preferences and constraints.

### Features
1. **Always-visible tab**: The Assignments tab is always present in the workshop view
2. **Empty state**: Shows helpful message and "Run Algorithm" button when no assignments exist
3. **Algorithm execution**: One-click algorithm run that distributes students across groups
4. **Visual feedback**: Color-coded capacity indicators for each group:
   - ✓ Green: Within capacity (between min and max)
   - ⚠ Yellow: Under minimum capacity
   - ✕ Red: Over maximum capacity
5. **Manual editing**: Dropdown per student to move them between groups (with AJAX updates)
6. **Warnings section**: Detailed display of errors and warnings:
   - 🔴 Errors: Unassigned students who couldn't fit in any group
   - 🟡 Warnings: Groups below minimum capacity
7. **Re-run capability**: Option to re-run algorithm (clears existing assignments with confirmation)
8. **Assignment tracking**: Records who assigned students (algorithm vs manual) and when

### Assignment Status
Workshops track their assignment state via `assignment_status` field:
- `none`: No assignments have been made
- `generated`: Algorithm has run
- `manually_edited`: Teacher has manually modified assignments

### Assignment Algorithm
**Implementation**: Priority-based greedy algorithm with dynamic priority adjustment (based on Java reference implementation)

**Algorithm Details**:
- **Service Layer**: Located in `app/Services/AssignmentAlgorithm/`
  - `AssignmentService`: Main orchestrator (equivalent to Java Main.java)
  - `GroupSorter`: Sorts groups by priority with configurable tie-breaking
  - `StudentSorter`: Sorts students by preference urgency
  - `ConstraintChecker`: Validates capacity and classroom mixing constraints
  - `AssignmentResult` DTO: Clean result handling with warnings

**Algorithm Phases**:
1. Load and initialize groups with popularity metrics
2. Sort groups by priority (lower number = higher priority)
3. Reorder student preferences based on sorted group order
4. Sort students by preference urgency (fewer preferences = more constrained = higher priority)
5. Execute assignment loop with constraint checking
6. Dynamic priority adjustment: when group reaches minimum, priority becomes PHP_INT_MAX - popularity (pushes to end but keeps popular ones relatively higher)

**Constraints**:
- Hard constraints: Maximum capacity per group
- Soft constraints: Classroom mixing limits (`max_students_from_one_classroom` field, nullable)

**Documentation**: See `/home/nikitas/programming/java/project-group-splitter-java/docs/ALGORITHM_IMPLEMENTATION.md` for detailed algorithm explanation

### Database Schema
- **Pivot table**: `groups_students` stores final assignments
- **Metadata fields**:
  - `assignment_method`: 'algorithm' or 'manual'
  - `assigned_at`: Timestamp of assignment
  - `assigned_by`: User ID who made the assignment

### Access
- Available as "Assignments" tab in workshop show/edit page
- Visible to all workshops regardless of assignment status
- Separate from the main workshop edit form

## Delete Operations

### Overview
The system provides comprehensive delete functionality for all major entities with safety measures to prevent accidental data loss.

### Features

**Workshop Deletion**:
- Delete button located at the bottom of workshop edit page (below main form, hidden on Assignments tab)
- Deletes workshop and all related data via database cascade:
  - All groups
  - All classrooms
  - All students
  - All group preferences
  - All assignments
- Modal confirmation showing counts of what will be deleted
- Success message displays deletion statistics
- Redirects to workshop index after deletion

**Group Deletion**:
- Inline "Delete" button in each group row (Groups tab)
- Removes group and unassigns all students from it
- Deletes all preferences for that group
- Modal shows count of students that will be unassigned
- Redirects to Groups tab after deletion

**Classroom Deletion**:
- Inline "Delete" button in each classroom row (Classrooms tab)
- Cascades to delete all students in the classroom
- Deletes student preferences and assignments
- Modal shows count of students that will be deleted
- Strong warning about data loss
- Redirects to Classrooms tab after deletion

**Student Deletion**:
- Inline "Delete" button in each student row (Students tab)
- Removes student, their preferences, and assignments
- Modal confirmation for each student
- Redirects to Students tab after deletion

**Clear All Assignments**:
- Red button in Assignments tab action bar
- Removes all student-group assignments without deleting students or groups
- Updates workshop status to 'none'
- Modal confirmation explaining that students/groups remain
- Redirects to Assignments tab after clearing
- Useful for re-running algorithm or starting fresh

### Safety Measures

1. **Modal Confirmations**: Every delete operation requires confirmation
2. **Warning Messages**: Modals show exactly what will be deleted with counts
3. **Authorization**: Users can only delete their own workshop data
4. **Cascade Information**: Clear messaging about related data that will be removed
5. **Success Feedback**: Deletion statistics shown after successful operation
6. **Tab Preservation**: Redirects return to the appropriate tab with hash fragment

### Database Behavior

All foreign key constraints use `cascadeOnDelete()`, ensuring:
- No orphaned records
- Automatic cleanup of related data
- Database integrity maintained
- No manual cascade logic needed in controllers

### Routes

```php
DELETE /workshops/{workshop}                        // Delete workshop
DELETE /workshops/{workshop}/groups/{group}         // Delete group
DELETE /workshops/{workshop}/classrooms/{classroom} // Delete classroom
DELETE /workshops/{workshop}/students/{student}     // Delete student
DELETE /workshops/{workshop}/clear-assignments      // Clear assignments
```

### Controllers

- **WorkshopController**: `destroy()`, `clearAssignments()`
- **GroupController**: `destroy()`
- **ClassroomController**: `destroy()`
- **StudentController**: `destroy()`

All controllers perform authorization checks and gather deletion statistics before removing data.

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

# Fresh migration (drops all tables and reseeds database to create user)
php artisan migrate:fresh --seed

```

### Testing

**Algorithm Tests**:
```bash
# Run all algorithm tests
php artisan test --group=algorithm

# Run a specific test
php artisan test --filter="simple perfect fit"

# Run with verbose output
php artisan test --group=algorithm --verbose

# Run all tests
php artisan test
```

**Test Fixtures**:
- Location: `tests/Feature/Assignment/Fixtures/`
- 6 CSV test scenarios covering: perfect fit, priority ordering, preference satisfaction, capacity constraints, classroom mixing, and dynamic priority
- Each test validates specific algorithm behaviors
- See `tests/Feature/Assignment/Fixtures/README.md` for detailed test case documentation

### Demo Credentials
**Default Admin User** (created by seeder):
- Email: `admin@admin.com`
- Password: `admin123`

**Sample Data** (created by seeder):
- 2 workshops with groups, classrooms, students, and preferences pre-populated for testing
- **Workshop 1: "Spring Project Workshop"** *(includes seeded assignments)*
  - 4 groups: Robotics, Art & Design, Music Production, Coding & Tech
  - 3 classrooms: 5A (15 students), 5B (16 students), 5C (14 students)
  - Total: 45 students with randomized group preferences
  - **Pre-assigned**: All students distributed across groups (round-robin) with `assignment_status = 'generated'`
- **Workshop 2: "Summer Activities 2026"** *(no assignments - for testing empty state)*
  - 3 groups: Sports & Athletics, Drama & Theater, Science Lab
  - 2 classrooms: 6A (12 students), 6B (13 students)
  - Total: 25 students with randomized group preferences
  - **No assignments**: Use this to test the "Run Algorithm" feature
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

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.1
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version-specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- In Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- **Important:** Always clear the config cache before running tests to ensure phpunit.xml environment settings are applied correctly: `php artisan config:clear && php artisan test --compact`.
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan config:clear && php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v3 rules ===

## Tailwind CSS 3

- Always use Tailwind CSS v3; verify you're using only classes supported by this version.
</laravel-boost-guidelines>
