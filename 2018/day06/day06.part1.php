<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $locations) : int {
    // Format location coordinates
    $locations = array_kvmap($locations, function (int $id, string $coords) : array {
        [$x, $y] = explode(', ', $coords);
        return ['x' => (int) $x, 'y' => (int) $y, 'area' => 0];
    });
    // Initialize the map
    $boundary = array_kvreduce($locations, function (int $boundary, int $id, array $location) {
        return max($boundary, $location['x'], $location['y']);
    }, 0);
    $map = array_fill_multi(2, $boundary + 1, null);
    // Guess what's the closest location for each point
    foreach (range(0, $boundary) as $y) {
        foreach (range(0, $boundary) as $x) {
            $distances = array_kvmap($locations, function (int $id, array $location) use ($x, $y) : int {
                return abs($x - $location['x']) + abs($y - $location['y']);
            });
            if (count(array_keys($distances, min($distances))) === 1) {
                $locations[kmin($distances)]['area'] += 1;
                $map[$y][$x] = kmin($distances);
            }
        }
    }
    // Remove infinite areas
    foreach (range(0, $boundary) as $x) if (isset($locations[$map[0][$x]])) unset($locations[$map[0][$x]]);
    foreach (range(0, $boundary) as $y) if (isset($locations[$map[$y][$boundary]])) unset($locations[$map[$y][$boundary]]);
    foreach (range($boundary, 0) as $x) if (isset($locations[$map[$boundary][$x]])) unset($locations[$map[$boundary][$x]]);
    foreach (range($boundary, 0) as $y) if (isset($locations[$map[$y][0]])) unset($locations[$map[$y][0]]);
    // Return the widest finite area
    return max(array_kvmap($locations, function (int $id, array $location) {
        return $location['area'];
    }));
};

echo $resolve([
    '1, 1',
    '1, 6',
    '8, 3',
    '3, 4',
    '5, 5',
    '8, 9',
]), "\n"; // 17

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 4016
