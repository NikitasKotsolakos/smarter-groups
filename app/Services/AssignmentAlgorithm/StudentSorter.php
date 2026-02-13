<?php

namespace App\Services\AssignmentAlgorithm;

use Illuminate\Support\Collection;

/**
 * Sorts students by preference urgency
 * Equivalent to Java's StudentComparator
 */
class StudentSorter
{
    /**
     * Sort students by preference urgency
     * Equivalent to Java's StudentComparator (StudentComparator.java)
     *
     * Algorithm:
     * 1. Shuffle students first (randomize base order)
     * 2. Sort by comparing preference priorities position-by-position
     * 3. If all compared priorities equal, fewer preferences comes first
     * 4. If everything equal, maintain random order from shuffle
     *
     * @param Collection $students Students with sortedPreferencePriorities set
     * @return Collection Sorted students
     */
    public function sortByPreferenceUrgency(Collection $students): Collection
    {
        // Step 1: Shuffle (equivalent to Java's Collections.shuffle - Main.java line 66)
        $shuffled = $students->shuffle();

        // Step 2: Sort by preference urgency (equivalent to Main.java line 69)
        return $shuffled->sort(function ($studentA, $studentB) {
            $prioritiesA = $studentA->sortedPreferencePriorities ?? [];
            $prioritiesB = $studentB->sortedPreferencePriorities ?? [];

            // Compare priorities position by position
            $minLength = min(count($prioritiesA), count($prioritiesB));

            for ($i = 0; $i < $minLength; $i++) {
                $comparison = $prioritiesA[$i] <=> $prioritiesB[$i];
                if ($comparison !== 0) {
                    return $comparison; // Return first difference found
                }
            }

            // If all compared priorities are equal, sort by number of preferences
            // Fewer preferences = more constrained = should go first
            $lengthComparison = count($prioritiesA) <=> count($prioritiesB);

            if ($lengthComparison !== 0) {
                return $lengthComparison;
            }

            // If everything is equal, maintain random order from shuffle
            // (PHP's sort is stable, so shuffle order is preserved)
            return 0;
        })->values(); // Re-index array
    }
}
