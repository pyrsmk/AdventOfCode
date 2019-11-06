<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $claims) : int {
    $fabric = array_fill_multi(2, 1000, 0);
    foreach ($claims as $claim) {
        preg_match('/^#(\d+) @ (\d+),(\d+): (\d+)x(\d+)$/', $claim, $matches);
        foreach (range($matches[3], $matches[3] + $matches[5] - 1) as $y) {
            foreach (range($matches[2], $matches[2] + $matches[4] - 1) as $x) {
                $fabric[$y][$x] += 1;
            }
        }
    }
    return array_reduce($fabric, function ($inches, $line) {
        return $inches + array_reduce($line, function ($inches, $square) {
            return $inches + (int) ($square > 1);
        }, 0);
    }, 0);
};

echo $resolve([
    '#1 @ 1,3: 4x4',
    '#2 @ 3,1: 4x4',
    '#3 @ 5,5: 2x2',
]), "\n"; // 4

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 96569
