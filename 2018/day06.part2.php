<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $locations, int $limit) : int {
    // Format location coordinates
    $locations = array_kvmap($locations, function (int $id, string $coords) : array {
        [$x, $y] = explode(', ', $coords);
        return ['x' => (int) $x, 'y' => (int) $y];
    });
    // Guess the size of the map
    $boundary = array_kvreduce($locations, function (int $boundary, int $id, array $location) {
        return max($boundary, $location['x'], $location['y']);
    }, 0);
    // Guess what's the total distance for each point
    $size = 0;
    foreach (range(0, $boundary) as $y) {
        foreach (range(0, $boundary) as $x) {
            $distance = array_kvreduce($locations, function (int $sum, int $id, array $location) use ($x, $y) : int {
                return $sum + (abs($x - $location['x']) + abs($y - $location['y']));
            }, 0);
            if ($distance < $limit) {
                $size += 1;
            }
        }
    }
    // Return the size of the safe area
    return $size;
};

echo $resolve([
    '1, 1',
    '1, 6',
    '8, 3',
    '3, 4',
    '5, 5',
    '8, 9',
], 32), "\n"; // 16

echo $resolve(
    file(__DIR__ . '/day06.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
    10000
), "\n"; // 46306
