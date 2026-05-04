# Assignment Algorithm

How students are assigned to groups when a teacher clicks **Run Algorithm** on a workshop's Assignments tab.

The implementation is a port of the Java reference at `/home/nikitas/programming/java/project-group-splitter-java`. The goal is a "good enough" assignment that a teacher can refine manually — not a global optimum.

## Implementation Map

| File | Role |
|---|---|
| `app/Services/AssignmentAlgorithm/AssignmentService.php` | Orchestrator — runs the six phases below |
| `app/Services/AssignmentAlgorithm/GroupSorter.php` | Sorts groups by priority (random tie-break by default; popularity-based variant available but unused) |
| `app/Services/AssignmentAlgorithm/StudentSorter.php` | Sorts students by preference urgency |
| `app/Services/AssignmentAlgorithm/ConstraintChecker.php` | Capacity + classroom-mixing checks; result validation |
| `app/Services/AssignmentAlgorithm/DTOs/AssignmentResult.php` | Returned to controller — groups, assignments, unassigned, warnings |

Entry point: `AssignmentService::assignStudentsToGroups(Workshop $workshop): AssignmentResult`.

## Inputs

Pulled from the workshop on call:

- **Groups** (`groups` table) with `priorityGroup` (lower = higher priority), `minimumParticipants`, `maximumParticipants`, `max_students_from_one_classroom` (nullable — defaults to `maximumParticipants` if null, i.e. no mixing constraint).
- **Students** (via `Classroom`) with eager-loaded `groupPreferences` ordered by `rank`, plus `classroom`.
- A per-group **popularity** count = number of students who listed the group as a preference (computed via `Group::getPopularity()`).

Preference rank is currently treated as ordering only: the algorithm tries preferences in the order returned, then re-orders them on the fly to match the *current* group priority order (see Phase 5). There is no separate ranked vs. unranked mode.

## Phases

1. **Load + initialize.** Fetch groups ordered by `priorityGroup`. Attach an in-memory `popularity` value and an empty `assignedStudents` collection to each. Fetch students with their preferences and classroom.
2. **Sort groups by priority** using `GroupSorter::sortByPriority` — ascending `priorityGroup` with `rand()` as tie-breaker.
3. **Reorder per-student preference priorities.** For each student, build a `sortedPreferencePriorities` array — the `priorityGroup` value of each preferred group, used by `StudentSorter`.
4. **Sort students by preference urgency** (`StudentSorter::sortByPreferenceUrgency`):
   - Shuffle first (random base order).
   - Compare `sortedPreferencePriorities` position-by-position: lower priority numbers come first.
   - On equal-prefix, **fewer preferences first** (more constrained students go earlier).
   - Otherwise preserve shuffle order (PHP's sort is stable).
5. **Assignment loop.** For each student in sorted order:
   - Re-order the student's preferences by the *current* `priorityGroup` of each target group (so groups that have been deprioritized are tried last).
   - Walk that list and assign to the first group that passes `ConstraintChecker::canAssignStudentToGroup`. Constraints: `count < maximumParticipants` **and** `count_from_same_classroom < max_students_from_one_classroom`.
   - When a group's count *equals* `minimumParticipants`, trigger **dynamic priority adjustment**.
   - If no preference fits, the student goes into the `unassigned` list (no fallback assignment).
6. **Extract + validate.** Flatten in-memory assignments to `[student_id, group_id]` pairs. Run `ConstraintChecker::validateResults`, which emits warnings for groups under minimum and errors for unassigned students.

### Dynamic Priority Adjustment

When a group hits `minimumParticipants`, its `priorityGroup` is set to `PHP_INT_MAX - popularity` and the group collection is re-sorted. Net effect:

- The group sinks to the back of priority order (so other under-min groups get filled next).
- Among groups that have hit minimum, *more popular* ones still rank higher than less popular ones (smaller subtraction → larger remainder).

This is what produces the "fill under-resourced groups first, then top up popular ones" behavior.

## Output

`AssignmentResult` returned to `WorkshopController`, which then writes the `groups_students` pivot rows with `assignment_method = 'algorithm'`, `assigned_at`, and `assigned_by = user_id`, and sets `workshop.assignment_status = 'generated'`.

Warnings (from `ConstraintChecker::validateResults`):

- `under_minimum` (severity `warning`) — group below its minimum.
- `unassigned` (severity `error`) — student couldn't fit any preferred group.

These drive the warnings panel on the Assignments tab.

## Tests

Pest fixtures at `tests/Feature/Assignment/Fixtures/` (CSV, semicolon-separated). Each is loaded through `runAlgorithmFixture()` in `tests/Feature/Assignment/AssignmentAlgorithmTest.php`:

| Fixture | What it verifies |
|---|---|
| `00-simple-test.csv` | Smoke test |
| `01-simple-perfect-fit.csv` | All students assignable, all assigned |
| `02-priority-ordering.csv` | Lower `priorityGroup` fills before higher |
| `03-preference-satisfaction.csv` | Students get a preferred choice when possible |
| `04-capacity-constraints.csv` | `maximumParticipants` is a hard ceiling |
| `05-classroom-mixing.csv` | `max_students_from_one_classroom` respected |
| `06-dynamic-priority.csv` | After hitting minimum, group is deprioritized |

Run: `php artisan test --filter=Assignment`.

## Known Limitations

- **No ranked vs. unranked mode.** Rank is read as ordering. No workshop flag to toggle behavior.
- **No fallback assignment.** A student who doesn't fit any preferred group is left unassigned — they aren't placed into an arbitrary group with capacity.
- **`GroupSorter::sortByPriorityAndPopularity` exists but is unused.** Reserved for a future tie-breaking strategy.
- **Synchronous execution.** Runs in the request cycle. Fine for hundreds of students; not load-tested at thousands.
