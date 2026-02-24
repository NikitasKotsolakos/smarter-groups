<?php

namespace App\Exports;

use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AssignmentsExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings, WithStyles
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
     * Get the data collection for export with blank rows between groups
     */
    public function collection()
    {
        $data = DB::table('groups_students')
            ->join('students', 'groups_students.student_id', '=', 'students.id')
            ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->join('groups', 'groups_students.group_id', '=', 'groups.id')
            ->where('groups.workshop_id', $this->workshop->id)
            ->select(
                'groups.name as group',
                'classrooms.name as classroom',
                'students.name as student'
            )
            ->orderBy('groups.name')
            ->orderBy('classrooms.name')
            ->orderBy('students.name')
            ->get();

        // Add blank rows between different groups
        $result = new Collection();
        $previousGroup = null;

        foreach ($data as $row) {
            // Add a blank row when the group changes (except for the first group)
            if ($previousGroup !== null && $previousGroup !== $row->group) {
                $result->push((object)[
                    'group' => '',
                    'classroom' => '',
                    'student' => ''
                ]);
            }

            $result->push($row);
            $previousGroup = $row->group;
        }

        return $result;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Assigned Group',
            'Classroom',
            'Student Name',
        ];
    }

    /**
     * Map each row of data
     */
    public function map($row): array
    {
        return [
            $row->group,
            $row->classroom,
            $row->student,
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Make the header row bold
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
