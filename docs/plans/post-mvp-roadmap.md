# Post-MVP Roadmap

Features that have been discussed but are not yet built. Extracted from the original `IMPLEMENTATION_PLAN.md` after it was retired. Items are grouped by theme, not ordered by priority — priority gets decided when work is picked up.

## Workshop lifecycle

- **Workshop status field** (`draft` / `active` / `completed`). Currently only `assignment_status` (`none` / `generated` / `manually_edited`) exists. A separate workshop-level status would let teachers archive finished workshops and hide them from the default list.
- **Copy / reuse workshop data.** Mentioned by the user as important for recurring workshops:
  - Copy students, groups, or classrooms from another workshop.
  - Copy an entire workshop structure (groups + classrooms + students, without preferences/assignments).
  - Avoids needing a global student/classroom database while still enabling reuse.

## Preference collection

- **Ranked vs. unranked mode toggle on the workshop.** The algorithm currently reads `rank` as ordering only. A workshop-level flag (`allow_ranking` or similar) plus algorithm changes would support the unranked use case the domain doc mentions (avoid popular-group overload, students don't feel bad about not getting "first choice").
- **Mixed ranking** (some preferences equal, some ranked) — extension of the above.
- **`max_preferences` workshop setting.** Currently hardcoded to 3 dropdowns in the student edit UI.

## CSV import polish

- **Downloadable CSV template** to give users a starting point.
- **Row-by-row validation and error reporting** for failed imports — currently errors abort the whole transaction without per-row diagnostics.

## Algorithm features

- **Wire up `GroupSorter::sortByPriorityAndPopularity`.** Already implemented; needs a workshop-level toggle and a way to pick the strategy.
- **Fallback assignment** for students who don't fit any preferred group (place them into any group with capacity rather than leaving unassigned).
- **Edit `max_students_from_one_classroom` from the group form.** The column exists on the `groups` table but isn't exposed in the create/edit UI — currently only settable via DB or seeders.
- **Weighted preferences.** Score-based ranking (e.g. 1st choice = 3 pts, 2nd = 2, 3rd = 1) so the algorithm can optimize total satisfaction rather than just feasibility.
- **Background job execution** via Laravel queues. Currently synchronous. Needed if/when workshops grow large enough to time out a request.
- **Multiple algorithm runs per workshop** — keep history, compare runs, pick the best. Requires a new schema (versioned assignments).
- **Advanced features.** Pairing preferences ("I want to be with X"), exclusions ("not with Y"), gender / skill-level balancing, friend-group balancing.

## Manual adjustment UX

- **Drag-and-drop** student reassignment as an alternative to the dropdown.
- **Bulk move** — move multiple students between groups in one action.
- **Undo / redo** for manual edits.
- **Real-time capacity validation** with stronger visual feedback during edits.

## Export & reporting

- **PDF export** with formatting (printable).
- **Email assignments to students.**
- **Statistics / dashboard**: preference satisfaction rate, group popularity, classroom distribution across groups, historical metrics across workshops.
- **Print-friendly view.**

## History & audit

- **Assignment history / changelog** per workshop.
- **Versioned assignments** — save, name, and restore different assignment attempts.
- **Revert** to a previous assignment.

## User & access

- **Multiple users per workshop** (co-teachers).
- **Role-based permissions** (admin, teacher, viewer).
- **Student login** so students submit their own preferences instead of the teacher entering them.

## Notifications

- Email students their assignments.
- Notify teachers when assignments are ready.
- Remind students to submit preferences (requires student login).

## Validation & code quality

- **Form-level validation** that `minimumParticipants <= maximumParticipants` on group create/edit, with errors rendered under the specific field.
- **Controller feature tests.** Workshop / Group / Classroom / Student / GroupPreferences controllers currently have no feature coverage; only auth, profile, and the algorithm have tests.
- **Performance testing** with 1000+ students to find the synchronous-execution ceiling.

## Cross-workshop / scheduling

- **Multi-workshop participation** — same student in multiple workshops, with conflict detection if workshops happen at the same time.
- **Time-slot management** for workshops.
- **Teacher assignment** of teachers to specific groups, with availability/specialty tracking.

## Potential ideas to think about

Less defined than the items above — speculative directions worth considering but not yet on the roadmap.

- **Assignment quality score.** Compute a single number per run (e.g. % of students placed in a top-N preference, weighted satisfaction) so multiple runs can be compared at a glance and the "best" picked.
- **Auto-merge / consolidate under-minimum groups.** When a group can't reach its minimum, suggest folding it into another group instead of just warning.
- **Concurrent-modification handling during algorithm runs.** Today nothing prevents a teacher from editing students while the algorithm executes. Could surface a loading overlay, lock the workshop, or wrap the run+save in a transaction with row-level locking.
- **Optimal-solver backend.** Replace the greedy algorithm with a linear-programming / CP-SAT solver (CPLEX, Gurobi, OR-Tools) for globally optimal assignments under complex constraints. Big jump in dependency footprint, only worth it if the greedy result becomes a real bottleneck.
