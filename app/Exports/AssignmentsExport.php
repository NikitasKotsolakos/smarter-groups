<?php

namespace App\Exports;

use App\Models\Workshop;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentsExport implements FromArray, WithCustomCsvSettings, WithStyles
{
    protected $workshop;
    protected $delimiter;

    public function __construct(Workshop $workshop, string $delimiter = ',')
    {
        $this->workshop = $workshop;
        $this->delimiter = $delimiter;
    }

    /**
     * Configure CSV settings
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->delimiter,
        ];
    }

    /**
     * Build the export data with groups as columns
     */
    public function array(): array
    {
        // Load groups with their students, sorted by classroom then name
        $groups = $this->workshop->groups()
            ->with(['students.classroom'])
            ->orderBy('name')
            ->get();

        if ($groups->isEmpty()) {
            return [['No groups found']];
        }

        // Sort students within each group by classroom name then student name
        foreach ($groups as $group) {
            $group->setRelation('students', $group->students->sortBy([
                ['classroom.name', 'asc'],
                ['name', 'asc'],
            ])->values());
        }

        $data = [];

        // First row: Group names (each spanning 2 columns, with empty column between groups)
        $headerRow1 = [];
        $groupIndex = 0;
        foreach ($groups as $group) {
            $headerRow1[] = $group->name;
            $headerRow1[] = ''; // Empty cell for the second column

            // Add empty separator column between groups (but not after the last one)
            if ($groupIndex < count($groups) - 1) {
                $headerRow1[] = '';
            }
            $groupIndex++;
        }
        $data[] = $headerRow1;

        // Second row: "Class" and "Student Name" for each group (with empty column between groups)
        $headerRow2 = [];
        $groupIndex = 0;
        foreach ($groups as $group) {
            $headerRow2[] = 'Class';
            $headerRow2[] = 'Student Name';

            // Add empty separator column between groups (but not after the last one)
            if ($groupIndex < count($groups) - 1) {
                $headerRow2[] = '';
            }
            $groupIndex++;
        }
        $data[] = $headerRow2;

        // Find the maximum number of students in any group
        $maxStudents = $groups->max(fn($group) => $group->students->count());

        // Data rows: One row per student index (with empty column between groups)
        for ($i = 0; $i < $maxStudents; $i++) {
            $row = [];
            $groupIndex = 0;
            foreach ($groups as $group) {
                if (isset($group->students[$i])) {
                    $student = $group->students[$i];
                    $row[] = $student->classroom->name;
                    $row[] = $student->name;
                } else {
                    // Empty cells if this group has fewer students
                    $row[] = '';
                    $row[] = '';
                }

                // Add empty separator column between groups (but not after the last one)
                if ($groupIndex < count($groups) - 1) {
                    $row[] = '';
                }
                $groupIndex++;
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        $groups = $this->workshop->groups()->orderBy('name')->get();

        if ($groups->isEmpty()) {
            return [];
        }

        $styles = [
            // Make both header rows bold
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];

        // Merge cells for group names in first row
        $columnIndex = 0;
        $groupIndex = 0;
        foreach ($groups as $group) {
            $startColumn = $this->getColumnLetter($columnIndex);
            $endColumn = $this->getColumnLetter($columnIndex + 1);
            $sheet->mergeCells("{$startColumn}1:{$endColumn}1");

            // Center-align the merged group name
            $sheet->getStyle("{$startColumn}1")->getAlignment()->setHorizontal('center');

            // Move to next group (2 columns for this group + 1 separator, except for last group)
            $columnIndex += 2;
            if ($groupIndex < count($groups) - 1) {
                $columnIndex += 1; // Skip the separator column
            }
            $groupIndex++;
        }

        return $styles;
    }

    /**
     * Convert column index to Excel column letter
     */
    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }
}
