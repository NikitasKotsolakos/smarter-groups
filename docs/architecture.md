# Architecture

How Smarter Groups is structured: domain, schema, features, code layout. Operational guidance for working on the codebase lives in `Claude.md`; the algorithm has its own deep dive in [algorithm.md](algorithm.md); features that aren't built yet are in [plans/post-mvp-roadmap.md](plans/post-mvp-roadmap.md).

## Project Purpose

Smarter Groups helps teachers and organizers assign participants (typically students) to groups within a workshop, based on participant preferences and capacity constraints.

The typical use case: a school runs a workshop where students from different classrooms can join various projects. Each student can join only one project per workshop but can express multiple preferences. The system produces a "good enough" starting assignment that the teacher can then refine manually — it's an iteration aid, not an oracle.

## Domain Model

```
Workshop (event/session)
  └── Groups (projects/activities offered in the workshop)
      └── Students (participants — assigned to one group)
  └── Classrooms (organizational units; students belong to one)
```

### Workshop
The overall event. Owned by a User.

| Field | Notes |
|---|---|
| `name` | |
| `user_id` | FK to User (creator/owner) |
| `assignment_status` | Enum: `none`, `generated`, `manually_edited` |

Relationships: `belongsTo User`, `hasMany Group`, `hasMany Classroom`.

### Group
A project/activity inside a workshop.

| Field | Notes |
|---|---|
| `name` | |
| `workshop_id` | FK |
| `minimumParticipants` | |
| `maximumParticipants` | |
| `priorityGroup` | Lower number = higher priority (filled first) |
| `max_students_from_one_classroom` | Nullable; `NULL` ⇒ no mixing constraint (defaults to `maximumParticipants` in the algorithm) |

Helper methods on the model:
- `getPopularity()` — count of students who selected this group as a preference.
- `getEffectiveMaxFromClassroom()` — `max_students_from_one_classroom ?? maximumParticipants`.
- `getCurrentCount()` — assigned students.
- `getCapacityStatus()` — `'ok'` / `'under'` / `'over'`.

Relationships: `belongsTo Workshop`, `belongsToMany Student` through `groups_students`, `hasMany GroupPreferences`.

### Student
A participant.

| Field | Notes |
|---|---|
| `name` | |
| `classroom_id` | FK — students belong to a classroom, not directly to a workshop |

Relationships: `belongsTo Classroom`, `hasMany GroupPreferences`, `belongsToMany Group` through `groups_students` (final assignments).

### Classroom
An organizational unit (e.g. "5A", "5B"). Per-workshop, not shared.

| Field | Notes |
|---|---|
| `name` | |
| `workshop_id` | FK |

Relationships: `belongsTo Workshop`, `hasMany Student`.

### GroupPreferences
A student's preference for a specific group.

| Field | Notes |
|---|---|
| `student_id` | FK |
| `group_id` | FK |
| `rank` | Nullable integer; ordering hint for the algorithm. See note below. |

Students currently express up to 3 preferences via the edit UI. The algorithm reads `rank` as ordering only — there's no separate ranked vs. unranked mode yet (see roadmap).

### User
Teacher/admin. `hasMany Workshop`. Students do not log in.

## Database Schema

### Tables
- `workshops`, `groups`, `students`, `classrooms`, `group_preferences`, `users`
- `workshop_classrooms` — pivot (Workshops ⟷ Classrooms)
- `groups_students` — pivot (Groups ⟷ Students), holds **final assignments**

### Pivot metadata
`groups_students` carries:
- `assignment_method` — `'algorithm'` or `'manual'`
- `assigned_at` — timestamp
- `assigned_by` — User ID

### Cascade behavior
All FKs use `cascadeOnDelete()`. Deleting a workshop wipes its groups, classrooms, students, preferences, and assignments without manual cleanup logic in controllers.

## Features

### CSV Import

Bulk-import a workshop's data from a semicolon-separated CSV. Format matches the Java reference implementation.

**Format**
- Separator: `;`
- Column 0: classroom name
- Column 1: student name (required)
- Columns 2+: group preferences — header row holds group names; each cell containing `1` marks a preference. Multiple `1`s per row are ranked left-to-right.

**Behavior**
- **Replaces all workshop data** before import (groups, classrooms, students, preferences, assignments). The confirmation dialog shows counts of what will be deleted.
- Groups created from header columns with defaults: min 8, max 15, priority 1.
- Classrooms created from unique values in column 0; students assigned by name.
- Preferences ranked by column order of `1` cells.
- Wrapped in a DB transaction — full rollback on error.
- Uploaded file deleted immediately after processing.
- Workshop `assignment_status` reset to `none`.

Entry point: "Import from CSV" button on the workshop page (auto-submits on file selection).

### Assignments Tab

Where the algorithm runs and results are managed. See [algorithm.md](algorithm.md) for the algorithm itself.

**UI behavior**
- Always visible on a workshop. Empty state shows "Run Algorithm" when no assignments exist.
- Per-group visual capacity indicator: ✓ green (within min/max), ⚠ yellow (under min), ✕ red (over max).
- Per-student dropdown for manual reassignment, AJAX-updated.
- Warnings panel: 🔴 unassigned students, 🟡 groups under minimum.
- Re-run with confirmation (clears existing assignments).
- Clear-all-assignments button (red) — removes pivot rows without deleting entities, sets status back to `none`.

**Status tracking** (`workshops.assignment_status`):
- `none` — nothing run yet
- `generated` — algorithm ran, no manual edits
- `manually_edited` — teacher has moved students

### Export

CSV (locale-aware delimiter — `;` for European locales, `,` for English/US) and Excel (xlsx, xls). Sorted by group → classroom → student name, blank rows between groups, bold headers in Excel.

Implemented via `maatwebsite/excel`, custom `AssignmentsExport` class.

Route: `GET /workshops/{workshop}/export-assignments?format=csv|xlsx|xls`.

### Delete Operations

All entities deletable via inline buttons; modal confirmations show counts before destruction.

| Target | Effect |
|---|---|
| Workshop | Cascades to groups, classrooms, students, preferences, assignments |
| Group | Removes group + unassigns its students + deletes preferences for it |
| Classroom | Cascades to students in it (and their preferences/assignments) |
| Student | Removes student, preferences, assignments |
| Clear assignments | Pivot rows only; entities preserved; status → `none` |

Authorization: users can only delete data in their own workshops. Tab is preserved on redirect (URL hash fragment).

Routes:

```
DELETE /workshops/{workshop}
DELETE /workshops/{workshop}/groups/{group}
DELETE /workshops/{workshop}/classrooms/{classroom}
DELETE /workshops/{workshop}/students/{student}
DELETE /workshops/{workshop}/clear-assignments
```

## Technical Stack

**Backend**
- Laravel 11.47, PHP 8.4
- MySQL (production and development). PostgreSQL is the longer-term preference but isn't currently in use due to an unresolved issue; some local setups may run Postgres.
- Laravel Breeze for auth

**Frontend**
- Vite 5, Tailwind CSS 3, Alpine.js 3, Axios 1.7
- Blade templates with Alpine for interactivity

**Testing & tooling**
- Pest 3 (with Laravel plugin) — only auth, profile, and the algorithm have test coverage today
- Laravel Pint
- Laravel Sail (optional)

## Code Layout

### Models
`app/Models/{Workshop,Group,Student,Classroom,GroupPreferences,User}.php`

### Controllers
`app/Http/Controllers/{Workshop,Group,Student,Classroom,GroupPreferences,Profile}Controller.php`

### Services
`app/Services/AssignmentAlgorithm/` — see [algorithm.md](algorithm.md) for the breakdown.

### Migrations
- Core schema: `2024_09_07_*` (workshops, classrooms, students, groups, group_preferences, workshop_classrooms, groups_students)
- `2026_01_04_222723_add_assignment_status_to_workshops_table.php`
- `2026_01_04_222746_add_metadata_to_groups_students_table.php`
- `2026_01_05_072312_add_classroom_mixing_to_groups_table.php`

## Naming Conventions

The Workshop → Group → Student hierarchy is **intentionally generic** so the system can be used outside the school/project context. Don't rename `Group` to `Project` (or similar domain-specific renames) without explicit user direction.
