# Algorithm Test Fixtures

This directory contains CSV test fixtures for testing the assignment algorithm. Each CSV file represents a specific test scenario that verifies a particular aspect of the algorithm's behavior.

## CSV Format

All CSV files follow the same format as the production CSV import:
- **Separator**: Semicolon (`;`)
- **Column 0**: Classroom name
- **Column 1**: Student name
- **Columns 2+**: Group names (header row), with "1" in cells to indicate preferences
- **Preference ranking**: Multiple "1"s in a row are ranked by column order (left to right = 1st, 2nd, 3rd choice)

## Test Cases

### 01-simple-perfect-fit.csv

**Purpose**: Verify all students get assigned when total capacity matches student count

**Setup**:
- 3 groups (A, B, C) with default capacity (8-15 each)
- 15 students total (5 per classroom)
- 3 classrooms
- Each student has 2 preferences

**Expected Results**:
- ✓ All 15 students should be assigned
- ✓ No unassigned students (errors)
- ✓ No warnings
- ✓ Each group should have exactly 5 students
- ✓ All capacity constraints respected

**What This Tests**: Basic functionality - algorithm can handle simple cases where everyone fits

---

### 02-priority-ordering.csv

**Purpose**: Verify that group priority is respected regardless of popularity

**Setup**:
- 3 groups with different priorities:
  - "Unpopular Priority 1" (priority 1, only 2 students prefer it)
  - "Popular Priority 2" (priority 2, 13 students prefer it)
  - "Popular Priority 3" (priority 3, 13 students prefer it)
- 15 students total
- Most students prefer groups 2 and 3

**Expected Results**:
- ✓ "Unpopular Priority 1" should fill first (despite low popularity)
- ✓ Priority ordering should be respected: 1 → 2 → 3
- ✓ All students assigned
- ✓ Demonstrates that priority overrides popularity

**What This Tests**: Group priority system - lower priority number = filled first

---

### 03-preference-satisfaction.csv

**Purpose**: Verify students get their preferred choices when capacity allows

**Setup**:
- 4 groups (Robotics, Art, Music, Sports) with ample capacity (8-15 each)
- 20 students with varied preferences
- Each student has 2 preferences
- Groups are roughly equally popular (5 students each)

**Expected Results**:
- ✓ All students assigned
- ✓ Most students should get 1st or 2nd choice
- ✓ Good preference satisfaction rate
- ✓ No capacity issues

**What This Tests**: Preference satisfaction - algorithm respects student preferences when possible

---

### 04-capacity-constraints.csv

**Purpose**: Verify algorithm handles insufficient capacity correctly

**Setup**:
- 3 groups labeled "Small Group A/B/C" (will be set to max 5 students each in test)
- 20 students total
- Total capacity = 15, but 20 students need assignment
- All students have 2 preferences

**Expected Results**:
- ✗ Some students will be unassigned (5 students)
- ✓ Error warnings should be generated for unassigned students
- ✓ No group should exceed maximum capacity (5)
- ✓ Capacity constraints strictly enforced

**What This Tests**: Hard capacity constraints and unassigned student handling

**Note**: Test must set `maximumParticipants = 5` for all groups to create the constraint

---

### 05-classroom-mixing.csv

**Purpose**: Verify classroom mixing constraint is enforced

**Setup**:
- 3 groups (Drama, Science, Sports) with mixing constraint (max 2 per classroom)
- 15 students from 3 classrooms (ClassroomA: 6, ClassroomB: 6, ClassroomC: 3)
- Many students from same classroom prefer same group
- Groups will have `max_students_from_one_classroom = 2`

**Expected Results**:
- ✓ No group should have more than 2 students from any single classroom
- ✓ Students from same classroom should be distributed
- ✓ All students assigned (capacity is sufficient if mixing constraint is respected)
- ✓ Mixing constraint prevents "classroom clustering"

**What This Tests**: Soft classroom mixing constraints

**Note**: Test must set `max_students_from_one_classroom = 2` for all groups

---

### 06-dynamic-priority.csv

**Purpose**: Verify dynamic priority adjustment when group reaches minimum capacity

**Setup**:
- 3 groups with different base priorities:
  - "Popular Low Priority" (priority 3, min 8, very popular - 14 preferences)
  - "Unpopular High Priority" (priority 1, min 8, unpopular - 8 preferences)
  - "Medium Priority" (priority 2, min 8, medium - 6 preferences)
- 15 students total
- Algorithm should prioritize filling "Unpopular" first due to priority

**Expected Results**:
- ✓ "Unpopular High Priority" fills first (priority 1)
- ✓ When it reaches minimum (8), it should move to back of queue
- ✓ "Medium Priority" should then fill (priority 2)
- ✓ "Popular Low Priority" fills last despite having most preferences
- ✓ Dynamic priority adjustment working correctly

**What This Tests**:
- Initial priority ordering (before minimum reached)
- Dynamic priority adjustment at minimum capacity
- PHP_INT_MAX - popularity calculation for deprioritized groups

---

## Running the Tests

```bash
# Run all algorithm tests
php artisan test --group=algorithm

# Run a specific test
php artisan test --filter="simple perfect fit"

# Run with verbose output
php artisan test --group=algorithm --verbose
```

## Adding New Test Cases

To add a new test case:

1. Create a new CSV file following the naming convention: `##-descriptive-name.csv`
2. Document it in this README with:
   - Purpose
   - Setup details
   - Expected results
   - What it tests
3. Add corresponding test in `tests/Feature/Assignment/AssignmentAlgorithmTest.php`
4. Run the test to verify it works

## Notes

- **Randomness**: The algorithm has random tie-breaking, so we test behavioral constraints (e.g., "capacity not exceeded") rather than exact assignments
- **Group Defaults**: Unless specified in test setup, groups use defaults:
  - `minimumParticipants = 8`
  - `maximumParticipants = 15`
  - `priorityGroup = 1` (or as specified in test description)
- **Import Behavior**: CSV import creates groups with defaults, so tests may need to update group settings after import
