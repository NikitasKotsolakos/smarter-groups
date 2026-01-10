<?php

namespace App\Services\AssignmentAlgorithm;

use Illuminate\Support\Collection;

/**
 * Sorts groups by priority with configurable tie-breaking strategies
 * Equivalent to Java's CompPrioRandom and CompPrioPopul comparators
 */
class GroupSorter
{
    /**
     * Sort groups by priority with random tie-breaking
     * Equivalent to Java's CompPrioRandom (CompPrioRandom.java)
     *
     * @param Collection $groups
     * @return Collection Sorted groups
     */
    public function sortByPriority(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            // Primary sort: priorityGroup (ascending - lower number = higher priority)
            // Secondary sort: random (for tie-breaking)
            return [$group->priorityGroup, rand()];
        })->values();
    }

    /**
     * Alternative: Sort by priority with popularity tie-breaking
     * Equivalent to Java's CompPrioPopul (CompPrioPopul.java)
     * FUTURE ENHANCEMENT - not used yet
     *
     * @param Collection $groups
     * @return Collection Sorted groups
     */
    public function sortByPriorityAndPopularity(Collection $groups): Collection
    {
        return $groups->sortBy(function ($group) {
            // Primary sort: priorityGroup (ascending)
            // Secondary sort: popularity (ascending - less popular first)
            return [$group->priorityGroup, $group->popularity];
        })->values();
    }
}
