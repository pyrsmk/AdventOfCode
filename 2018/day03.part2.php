<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $claims) : int {
    $fabric = array_fill_multi(2, 1000, '.');
    $ids = [];
    $overlaps = [];
    foreach ($claims as $claim) {
        preg_match('/^#(\d+) @ (\d+),(\d+): (\d+)x(\d+)$/', $claim, $matches);
        $ids[] = $matches[1];
        foreach (range($matches[3], $matches[3] + $matches[5] - 1) as $y) {
            foreach (range($matches[2], $matches[2] + $matches[4] - 1) as $x) {
                if ($fabric[$y][$x] !== '.') {
                    if (!in_array($fabric[$y][$x], $overlaps)) $overlaps[] = $fabric[$y][$x];
                    if (!in_array($matches[1], $overlaps)) $overlaps[] = $matches[1];
                };
                $fabric[$y][$x] = $matches[1];
            }
        }
    }
    return (int) current(array_diff($ids, $overlaps));
};

echo $resolve([
    '#1 @ 1,3: 4x4',
    '#2 @ 3,1: 4x4',
    '#3 @ 5,5: 2x2',
]), "\n"; // 3

echo $resolve(
    file(__DIR__ . '/day03.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 1023
