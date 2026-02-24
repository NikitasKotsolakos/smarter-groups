# Group Splitter - Implementation Plan

> **Status**: MVP Development - Assignments Feature Complete
> **Last Updated**: 2026-01-05
> **Related**: See [Claude.md](../Claude.md) for domain model and project overview

## Current Implementation Status

### Completed ✓

#### Infrastructure & Setup
- Basic Laravel 11 setup with PHP 8.4
- All migrations run successfully
- Laravel Breeze installed for authentication
  - User registration functional
  - User login/logout functional
- Database seeder functional
  - Creates default admin user (admin@admin.com / admin123)
  - Seeds 2 sample workshops with groups, classrooms, students, and preferences for testing
  - Workshop 1: 45 students across 3 classrooms with 4 groups (with seeded assignments)
  - Workshop 2: 25 students across 2 classrooms with 3 groups (no assignments - for testing)
  - Students have randomized preferences (1-3 choices each)
  - Workshop 1 includes pre-generated assignments to test the Assignments tab
  - Run with: `php artisan db:seed` or `php artisan migrate:fresh --seed`
- Frontend: Vite + Tailwind CSS + Alpine.js

#### Data Models
- All models created:
  - Workshop, Group, Student, Classroom, GroupPreferences, User
- Basic CRUD controllers exist:
  - WorkshopController
  - GroupController
  - StudentController
  - ClassroomController
  - GroupPreferencesController
  - ProfileController

#### Features Implemented
- **Workshop Management** (Complete for MVP):
  - ✓ Users can create workshops with groups and their parameters
  - ✓ Users can view individual workshop details
  - ✓ Users can see list of all their workshops
  - ✓ Users can edit workshop name, group details, and classrooms
  - ✓ Workshops are user-scoped (each user sees only their own workshops)
  - ✓ Workshop listing shows name, group count, and creation date
  - ✓ Navigation includes "My Workshops" link
  - ✓ Tab-based UI for managing Groups, Classrooms, and Students
  - ✓ Groups can be created with min/max participants and priority settings
  - ✓ Groups can be edited (name, min/max, priority)
  - ✓ Classrooms can be created and edited (name)
  - ✓ Classrooms are per-workshop (not shared across workshops)
  - ✓ Students can be created with name and classroom assignment
  - ✓ Students can be edited (name, classroom, preferences)
  - ✓ Student preferences can be set (3 ranked choices via dropdowns)
  - ✓ Students are per-workshop (not shared across workshops)

- **CSV Import** (Complete):
  - ✓ Bulk import groups, classrooms, students, and preferences from CSV file
  - ✓ Semicolon-separated format matching Java reference implementation
  - ✓ Auto-creates groups with default capacity settings
  - ✓ Assigns students to classrooms and creates ranked preferences
  - ✓ Transaction-safe with file cleanup
  - ✓ Available via "Import from CSV" button on workshop page

- **Assignments Feature** (Complete for MVP):
  - ✓ Assignments tab always visible in workshop view
  - ✓ Empty state with "Run Algorithm" button when no assignments
  - ✓ Database schema supports assignment tracking (`assignment_status` on workshops)
  - ✓ Pivot metadata tracks assignment method (algorithm/manual), timestamp, and user
  - ✓ Algorithm execution (basic round-robin implementation for MVP)
  - ✓ Visual capacity indicators (✓ Green, ⚠ Yellow, ✕ Red) for each group
  - ✓ Manual editing via dropdown per student
  - ✓ Move students between groups one at a time
  - ✓ Warnings section for unassigned students and capacity issues
  - ✓ Re-run algorithm capability with confirmation dialog
  - ✓ Assignment status tracking (none/generated/manually_edited)
  - ✓ Model helper methods for capacity checking and status
  - ✓ Routes: POST /run-algorithm, PUT /students/{id}/assignment
  - ✓ Controller methods in WorkshopController

- **Export Functionality** (Complete):
  - ✓ Export assignments to CSV with locale-aware delimiters
    - Semicolon (;) for European locales (de, fr, es, it, pt, nl, pl, cs, el, hu, ro, sv, da, no, fi)
    - Comma (,) for English/US locales
  - ✓ Export assignments to Excel (XLSX and XLS formats)
  - ✓ Assignments sorted by Group, then Classroom, then Student Name
  - ✓ Empty rows between different groups for readability
  - ✓ Bold column headers in Excel exports
  - ✓ Timestamped filenames with slugified workshop names
  - ✓ Export buttons on Assignments tab (separate buttons for CSV and Excel)
  - ✓ Uses laravel-excel (maatwebsite/excel) package
  - ✓ Custom export class (AssignmentsExport) with styling support
  - ✓ Route: GET /workshops/{workshop}/export-assignments?format=csv|xlsx|xls

### Not Yet Implemented ✗

#### Core Features (Critical)
- **Advanced Assignment Algorithm** (current: basic round-robin stub)
  - Preference-based optimization (currently ignores preferences)
  - Priority group handling
  - Ranked vs unranked preference modes
  - Classroom mixing (optional)
  - Constraint satisfaction optimization

#### User Interface & Workflows
- Delete operations for workshops, groups, classrooms, and students
- Basic validation improvements (prevent min > max on groups, better error messages)

#### Business Logic
- Most business logic beyond basic CRUD
- Preference validation
- Assignment validation and conflict detection
- Bulk operations (import/export)

## Algorithm Requirements

### Reference Implementation
- **Location**: `/home/nikitas/programming/java/project-group-splitter-java`
- **Current Format**: CSV input
- **Task**: Algorithm logic needs to be ported to PHP/Laravel

### Algorithm Features Required

1. **Preference Matching**: Assign students to their preferred groups when possible
   - Must handle both ranked and unranked preferences
   - **Ranked mode**: 1st choice, 2nd choice, 3rd choice
   - **Unranked mode**: All preferences treated equally (to avoid popular group overload and student disappointment)
   - **Mixed mode**: Some preferences may share the same rank

2. **Capacity Constraints**: Respect min/max participants for each group
   - Hard constraint: Cannot exceed maximum
   - Hard constraint: Must meet minimum (or close/adjust)

3. **Priority Groups**: Fill higher-priority groups first (useful for less popular groups)
   - Groups with higher priority values get filled before lower priority ones

4. **Classroom Mixing** (optional feature): Try to mix students from different classrooms
   - Configurable per workshop
   - Soft constraint (nice-to-have, not required)

5. **Optimization Goal**: Maximize overall satisfaction while respecting constraints
   - Balance between giving students their preferences and filling all groups

6. **Output**: A "good enough" initial assignment that teachers can manually refine
   - Not seeking perfection, but a good starting point

### Algorithm Inputs
- List of students with their group preferences (may be ranked, unranked, or mixed ranking)
- List of groups with min/max capacity and priority levels
- Optional: classroom mixing preference flag
- Optional: whether preferences are ranked or unranked for this workshop

### Algorithm Output
- Assignment of each student to exactly one group
- Should respect all hard constraints (min/max capacity)
- Should optimize for preferences and priorities
- Metadata about the assignment quality (e.g., how many got 1st choice, 2nd choice, etc.)

## Known Issues / TODOs

### Database Schema Issues

1. ✓ **GroupPreferences rank field** - COMPLETED
   - Added `rank` field (nullable integer)
   - Supports ranked preferences (1 = 1st choice, 2 = 2nd choice, 3 = 3rd choice)
   - Can be extended to support unranked mode in the future (all null or all same value)

2. **Workshop table enhancements**
   - ✓ `user_id` foreign key: COMPLETED - Workshops now belong to users
   - `allow_ranking` boolean: Control if students can rank preferences for this workshop
   - `allow_classroom_mixing` boolean: Whether to try mixing classrooms in algorithm
   - `status` field: Track if workshop is draft/active/completed
   - `max_preferences` integer: How many groups can a student select (default 2-3)

3. **GroupsStudents pivot table enhancements**
   - `assigned_at` timestamp: Track when assignment was made
   - `manually_edited` boolean: Track if teacher manually moved this student
   - `assigned_by` string: 'algorithm' or 'manual' or user_id

4. **Groups table enhancements**
   - Validate that `minimumParticipants <= maximumParticipants`
   - Consider default values for priority

### Model Relationship Issues

1. ✓ Workshop → User relationship: COMPLETED (belongsTo)
2. ✓ Workshop → Classrooms relationship: COMPLETED (hasMany)
3. ✓ Workshop → Students relationship: COMPLETED (hasManyThrough Classroom)
4. ✓ Student → Classroom relationship: COMPLETED (belongsTo)
5. ✓ Student → GroupPreferences relationship: COMPLETED (hasMany)
6. ✓ Classroom → Workshop relationship: COMPLETED (belongsTo)
7. Group model doesn't define relationship to Students (will be needed for assignment results)

### Code Quality Issues

1. Missing model factories for testing
2. Missing seeders for realistic test data
3. No tests written yet
4. Controllers may need refactoring after requirements are finalized
5. **Error handling and validation UI needs improvement**:
   - Current error display is functional but not polished
   - Custom validation errors (e.g., min > max) don't appear under specific fields
   - Error messages should be more user-friendly and positioned better
   - Consider improving visual feedback for validation errors

## Implementation Decisions to Make

### Preference Ranking System
- **Decision Required**: Exact UI/UX for how students select preferences
  - Drag-and-drop ranking?
  - Checkboxes with optional numeric ranking?
  - Different interfaces for ranked vs unranked mode?
- **Decision Required**: How to store rank values
  - 1, 2, 3 for rankings?
  - All NULL for unranked?
  - All same value (e.g., all 1) for unranked?

### Student Data Entry
- **Decision Required**: How do teachers add students?
  - Manual entry form?
  - CSV bulk import?
  - Integration with school systems?
  - All of the above?

### Assignment Algorithm Approach
- **Decision Required**: Algorithm implementation strategy
  - Port Java code directly to PHP?
  - Rewrite using a different approach?
  - Use existing PHP libraries for optimization?
- **Decision Required**: Where to run the algorithm
  - Synchronous request (may be slow for large datasets)?
  - Background job (Laravel queue)?
  - Store results and allow re-running?

### Manual Adjustment Interface
- **Decision Required**: UI for teachers to adjust assignments
  - Drag-and-drop students between groups?
  - Click student → dropdown to select new group?
  - Bulk operations (swap groups, etc.)?
  - Real-time capacity validation?

## MVP Scope

### Definition of MVP
A simple, focused application that allows teachers to complete the entire workflow of creating a workshop, adding students and groups, collecting preferences, running the assignment algorithm, and viewing/exporting results with basic manual adjustment capabilities.

**Key Principles:**
- Simple approach without many advanced parameters and options
- No advanced user management (basic auth is sufficient)
- All basic workflow functionality must work end-to-end
- Students and classrooms are per-workshop (self-contained, no sharing between workshops)
- Focus on getting it working, not perfect

### Core MVP Features

#### 1. User Management (Basic)
- ✓ User registration (already implemented)
- ✓ User login/logout (already implemented)
- Single user type (teacher/admin - no role differentiation needed)
- **Tweaking needed**: Review if current implementation needs any adjustments

#### 2. Workshop Management
- ✓ User can create a workshop (implemented)
- ✓ User can see list of all their workshops (implemented)
- ✓ User can see details of a specific workshop (implemented)
- ✓ Workshops are scoped to users (user_id foreign key added)
- ✓ User can edit workshop details (name and groups) (implemented)
- **Pending**: Workshop status tracking (draft, active, completed)

#### 3. Group Management (within a workshop)
- ✓ User can create groups for a workshop (implemented)
- ✓ User can edit existing groups (name, min/max participants, priority) (implemented)
- **Pending**: User can delete groups (with validation - can't delete if has assigned students)
- ✓ Visual display of group parameters in table (implemented)

#### 4. Student Management (per-workshop)
- ✓ User can add students to a workshop (manual entry form) (implemented)
- ✓ User can edit student details (name, classroom) (implemented)
- ✓ Students belong to a specific workshop (not shared across workshops) (implemented)
- ✓ Simple table view of all students in a workshop (implemented)
- ✓ Tab-based UI for managing students (implemented)
- **Pending**: User can delete students (with validation - warn if has preferences)

#### 5. Classroom Management (per-workshop)
- ✓ User can create classrooms for a workshop (implemented)
- ✓ User can edit classroom names (implemented)
- ✓ Classrooms are per-workshop (not shared) (implemented)
- ✓ Tab-based UI for managing classrooms (implemented)
- **Pending**: User can delete classrooms
- **Pending**: User can assign students to classrooms (in student management)

#### 6. Preference Collection
- ✓ User can set preferences for each student (implemented)
- ✓ Student can select up to 3 groups (1st, 2nd, 3rd choice) (implemented)
- ✓ Preferences are ranked (1st, 2nd, 3rd choice via dropdowns) (implemented)
- ✓ UI: Dropdown selects for each preference rank (implemented)
- ✓ All preferences are optional (can leave empty) (implemented)
- **Note**: Implemented with ranking for MVP (can add unranked mode later if needed)

#### 6b. Bulk Data Import
- ✓ CSV import functionality (implemented)
  - User can upload CSV file with semicolon separator
  - Automatically creates groups, classrooms, students, and preferences
  - Groups extracted from header row (columns 3+)
  - Classrooms from column 1
  - Students from column 2, assigned to classrooms
  - Preferences where cell contains "1" (ranked by order)
  - File deleted immediately after processing
  - Default group values: 8-15 participants, priority 1
- **Pending**: Provide downloadable CSV template for users
- **Pending**: Bulk import validation and error reporting (row-by-row)

#### 7. Assignment Algorithm
- **New**: Port specific algorithm from Java implementation (`/home/nikitas/programming/java/project-group-splitter-java`)
- Algorithm considers:
  - Student preferences (unranked - all equal)
  - Group capacity (min/max participants)
  - Group priority (fill higher-priority groups first)
  - Group popularity (distribute students to avoid overloading popular groups)
- **No classroom mixing** for MVP (can be added later)
- **No preference ranking** for MVP (all preferences treated equally)
- User triggers algorithm with a "Run Assignment" button
- Algorithm runs synchronously (background jobs can be added later if needed)

#### 8. Results Viewing & Manual Adjustment
- **New**: Display assignment results in a clear, organized format
  - Show which students are assigned to which groups
  - Show group capacity status (e.g., "12/15 students" with color coding)
  - Color indicators: Green (within limits), Yellow (under minimum), Red (over maximum)
- **New**: Basic manual editing interface
  - Click student → dropdown/button to reassign to different group
  - **Override capacity limits**: Allow moving students even if breaks min/max
  - **Visual warnings**: Still show capacity violations, but don't prevent them
  - Real-time capacity updates as students are moved
- **New**: Export results
  - Export to TXT file (simple formatted text)
  - Export to CSV file (student, group, classroom columns)

#### 9. Data Scope & Isolation
- Each workshop is completely self-contained
- Students belong to one workshop
- Classrooms belong to one workshop
- Groups belong to one workshop
- No data sharing between workshops (keeps historical data clean)

### MVP Exclusions (Post-MVP Features)

#### Deferred to v2+
1. **Preference Ranking**
   - Allow students to rank preferences (1st, 2nd, 3rd choice)
   - Mixed ranking modes (some equal, some ranked)
   - Algorithm optimization for ranked preferences

2. **Classroom Mixing in Algorithm**
   - Try to balance students from different classrooms in each group
   - Per-workshop preference flag for mixing

3. **Advanced Student Management**
   - Bulk import from CSV
   - Copy students from another workshop
   - Global student database with workshop enrollment

4. **Advanced Workshop Management**
   - Copy entire workshop (groups, students, structure)
   - Workshop templates
   - Archive/restore functionality

5. **User Management**
   - Multiple teachers/users per workshop
   - Role-based permissions (admin, teacher, viewer)
   - Student login to submit their own preferences

6. **Algorithm Enhancements**
   - Multiple algorithm options to choose from
   - Algorithm parameters/tuning
   - Compare multiple algorithm runs
   - Background job processing for large datasets
   - Undo/redo algorithm runs

7. **Results Features**
   - PDF export with formatting
   - Email results to students
   - Print-friendly view
   - Statistics dashboard (satisfaction rates, preference metrics)

8. **UI/UX Polish**
   - Drag-and-drop for manual adjustments
   - Undo/redo for manual changes
   - Assignment history/changelog
   - Keyboard shortcuts
   - Mobile-responsive design

9. **Data Management**
   - Import/export entire workshops
   - Backup/restore
   - Data validation and cleanup tools

### MVP Data Model Implications

#### Required Schema Changes
1. ✓ **Workshops table**: Add `user_id` foreign key - COMPLETED
2. **Students table**: Add `workshop_id` foreign key (students are per-workshop)
3. **Classrooms table**: Add `workshop_id` foreign key (classrooms are per-workshop)
4. **GroupPreferences table**: Add `rank` field (nullable, default null for MVP unranked mode)
5. **Workshops table**: Add `status` field (draft/active/completed)
6. **GroupsStudents table**: Consider adding:
   - `assigned_by`: 'algorithm' or 'manual'
   - `assigned_at`: timestamp

#### Relationships to Update
- ✓ Workshop → belongsTo User - COMPLETED
- ✓ User → hasMany Workshops - COMPLETED (implicit)
- Workshop → hasMany Students
- Workshop → hasMany Classrooms
- Student → belongsTo Workshop

### MVP User Workflow (Happy Path)

1. **Teacher registers/logs in** (already works)
2. **Teacher creates a new workshop** (already works)
   - Enters workshop name
   - Sets status to "draft"
3. **Teacher creates groups for the workshop** (already works)
   - Adds 3-5 groups with names
   - Sets min/max participants for each (e.g., min: 8, max: 15)
   - Sets priority levels (e.g., 1-5, higher = fill first)
4. **Teacher creates classrooms**
   - Adds classrooms (e.g., "Class 5A", "Class 5B", "Class 5C")
5. **Teacher adds students**
   - Manually enters student names
   - Assigns each student to a classroom
6. **Teacher collects student preferences**
   - For each student, selects 2-3 groups they prefer
   - All selections have equal weight (no ranking)
7. **Teacher runs the assignment algorithm**
   - Clicks "Run Assignment" button
   - Algorithm processes and assigns students to groups
8. **Teacher reviews results**
   - Sees which students are in which groups
   - Sees capacity indicators (green/yellow/red)
   - Identifies any issues
9. **Teacher manually adjusts** (if needed)
   - Moves specific students between groups
   - System shows capacity updates in real-time
10. **Teacher exports results**
    - Downloads CSV for records
    - Downloads TXT for sharing
11. **Teacher marks workshop as "completed"**

### MVP Success Criteria

A successful MVP must:
- ✓ Allow teacher to complete entire workflow without errors
- ✓ Produce reasonable assignments that respect preferences
- ✓ Provide clear visibility into group capacity status
- ✓ Allow easy manual corrections
- ✓ Export results in usable formats
- ✓ Handle a workshop with ~100 students and ~5 groups
- ✓ Be intuitive enough to use without documentation

### MVP Timeline & Milestones

**Estimated Timeline**: TBD based on implementation discussion

**Suggested Milestones**:
1. **Phase 1**: Data model updates + migrations
2. **Phase 2**: Student/Classroom CRUD interfaces
3. **Phase 3**: Preference collection interface
4. **Phase 4**: Algorithm implementation (port from Java)
5. **Phase 5**: Results viewing + manual adjustment interface
6. **Phase 6**: Export functionality
7. **Phase 7**: Testing + bug fixes
8. **Phase 8**: Polish + deployment

## Future Enhancements (Post-MVP)

### High Priority Post-MVP Features

1. **Copy/Reuse Workshop Data** (mentioned by user as important)
   - Copy students from one workshop to another
   - Copy groups from one workshop to another
   - Copy classrooms from one workshop to another
   - Copy entire workshop structure (groups + students + classrooms)
   - Helps with recurring workshops or similar setups
   - Avoids need for global student/classroom database while still enabling reuse

2. **Preference Ranking System**
   - Allow students to rank their preferences (1st, 2nd, 3rd choice)
   - Update algorithm to optimize for ranked preferences
   - Workshop-level setting to enable/disable ranking
   - Mixed ranking modes (some preferences equal, some ranked)

3. **Classroom Mixing in Algorithm**
   - Try to balance students from different classrooms in each group
   - Per-workshop preference flag for classroom mixing
   - Soft constraint in algorithm

### Additional Features Under Consideration

4. **Multi-workshop support**: Can a student participate in multiple workshops?
   - Probably yes, but only one group per workshop
   - May need scheduling/conflict detection

5. **Time slots**: Do workshops happen at specific times that might conflict?
   - Would require schedule management
   - Conflict detection across workshops

6. **Teacher assignments**: Should certain groups have specific teacher assignments?
   - Teacher availability
   - Teacher preferences/specialties

7. **Enhanced Export/Import**:
   - PDF reports for printing
   - Bulk import of students from CSV
   - Integration with school information systems
   - Import/export entire workshops

8. **History/Versioning**:
   - Track changes to assignments over time
   - Ability to revert to previous assignments
   - Compare different algorithm runs
   - Save multiple assignment versions per workshop
   - Name and annotate different assignment attempts

9. **Assignment Enhancements**:
   - CSV template download for bulk imports
   - Bulk import validation and better error reporting
   - Drag-and-drop student reassignment (alternative to dropdowns)
   - Assignment metrics (preference satisfaction rate, balance scores)
   - Undo/redo for manual edits
   - Bulk move operations (move multiple students at once)

10. **Notifications**:
   - Email students their assignments
   - Notify teachers when assignments are ready
   - Remind students to submit preferences

11. **Analytics/Reporting**:
   - Dashboard showing preference satisfaction rates
   - Group popularity metrics
   - Classroom distribution reports
   - Historical data across multiple workshops

12. **Advanced Algorithm Features**:
   - Student pairing preferences ("I want to be with X")
   - Student exclusion preferences ("Don't put me with Y")
   - Gender balancing
   - Skill level balancing
   - Friend group detection and balancing

## Testing Strategy

### Algorithm Testing
- Test with small datasets (10-20 students)
- Test with medium datasets (100-200 students)
- Test edge cases:
  - All students prefer same group
  - Groups at minimum capacity
  - Groups at maximum capacity
  - More students than total capacity
  - Fewer students than total minimum requirements
  - Uneven preference distributions

### Integration Testing
- Full workflow from workshop creation to assignment to manual adjustment
- Multi-user scenarios
- Data validation at each step

### Performance Testing
- Algorithm performance with large datasets (1000+ students)
- UI responsiveness with many groups/students
- Database query optimization

## Development Workflow

### Phase 1: Data Model Refinement
*Details to be added based on MVP discussion*

### Phase 2: Algorithm Implementation
*Details to be added based on MVP discussion*

### Phase 3: UI/UX Implementation
*Details to be added based on MVP discussion*

### Phase 4: Testing & Refinement
*Details to be added based on MVP discussion*

### Phase 5: Deployment
*Details to be added based on MVP discussion*

## Technical Debt & Refactoring Needs

*To be tracked as implementation progresses*

## Questions for User / Clarifications Needed

1. MVP scope - what features are absolutely essential?
2. Primary user workflow - what's the typical step-by-step process?
3. Expected scale - how many students/workshops typically?
4. Deployment target - self-hosted? Cloud? Requirements?
5. Student interface - do students directly enter preferences, or does teacher enter them?
