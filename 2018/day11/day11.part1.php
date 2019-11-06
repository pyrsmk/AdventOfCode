<?php

require __DIR__ . '/../functions.php';

$resolve = function (int $serial) : string {
    // Initialize power cells
    foreach (range(1, 300) as $y) {
        foreach (range(1, 300) as $x) {
            $cells[$y][$x] = (($x + 10) * $y + $serial) * ($x + 10) / 100 % 10 - 5;
        }
    }
    // Search for the best power cells
    $best = [];
    foreach (range(1, 300 - 2) as $y) {
        foreach (range(1, 300 - 2) as $x) {
            $score = $cells[$y][$x] + $cells[$y][$x + 1] + $cells[$y][$x + 2]
                + $cells[$y + 1][$x] + $cells[$y + 1][$x + 1] + $cells[$y + 1][$x + 2]
                + $cells[$y + 2][$x] + $cells[$y + 2][$x + 1] + $cells[$y + 2][$x + 2];
            if (!isset($best['score']) || $score > $best['score']) {
                $best['score'] = $score;
                $best['x'] = $x;
                $best['y'] = $y;
            }
        }
    }
    return "{$best['x']},{$best['y']}";
};

echo $resolve(18), "\n"; // 33,45
echo $resolve(42), "\n"; // 21,61

echo $resolve(1308), "\n"; // 21,41
