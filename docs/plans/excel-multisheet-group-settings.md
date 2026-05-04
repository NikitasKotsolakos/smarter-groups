Proposed Multi-Sheet Excel Structure
Sheet 1: groups (Configuration)
name	minimumParticipants	maximumParticipants	priorityGroup
Group A	3	8	1
Group B	5	10	2
Group C	2	6	3
Sheet 2: students (Data + Preferences)
Classroom	Student	Group A	Group B	Group C
Class 1	Alice	1	1	1
Class 1	Bob	1		
Class 2	Ivy		1	1
Feasibility Analysis
✅ Advantages
Already supported - The project uses maatwebsite/excel which natively handles multi-sheet workbooks
User-friendly - Non-technical users can open .xlsx in Excel, Google Sheets, LibreOffice
Single file - Everything in one place, no file linking issues
Auto-generation possible - Sheet 2 could auto-populate group columns from Sheet 1 headers
Validation - Can validate Sheet 1 before processing Sheet 2
⚠️ Considerations
Column order dependency - Preferences in Sheet 2 must match group names in Sheet 1
Delimiter ambiguity - Excel uses commas internally; semicolons (European locale) could cause issues
Empty cell handling - Need to distinguish between "no preference" and "empty cell"
Recommended Implementation
Option A: Two explicit sheets (most explicit)
Sheet "groups":
  name | minimumParticipants | maximumParticipants | priorityGroup
  Group A | 3 | 8 | 1
  Group B | 5 | 10 | 2

Sheet "students":
  Classroom | Student | Group A | Group B
  Class 1 | Alice | 1 | 1

Option B: Single sheet with auto-generated preferences (simpler for users)
Sheet "groups":
  name | minimumParticipants | maximumParticipants | priorityGroup | Student 1 | Student 2 | Student 3
  Group A | 3 | 8 | 1 | Alice | Bob | Ivy
  Group B | 5 | 10 | 2 | | | 
  Group C | 2 | 6 | 3 | | | 

  → Preferences auto-generated: if a student name appears in a group row, they have a preference for it

Option C: Hybrid (best UX)
Sheet "groups":
  name | minimumParticipants | maximumParticipants | priorityGroup
  Group A | 3 | 8 | 1
  Group B | 5 | 10 | 2

Sheet "students":
  Classroom | Student | Preference 1 | Preference 2
  Class 1 | Alice | Group A | Group B
  Class 1 | Bob | Group B | Group A

This uses group names as references instead of "1" values, making it more readable.

Implementation Plan
If you proceed, the implementation would involve:

New import endpoint in WorkshopController for Excel multi-sheet
Excel import class (using maatwebsite/excel's ToCollection interface) to read both sheets
Validation logic to ensure group names in Sheet 2 match Sheet 1
Export template for users to download as a starting point
