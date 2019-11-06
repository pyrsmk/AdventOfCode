<?php

require __DIR__ . '/../functions.php';

$resolve = function (int $serial) : string {
    // Initialize power cells
    foreach (range(1, 300) as $y) {
        foreach (range(1, 300) as $x) {
            $cells[$y][$x] = (($x + 10) * $y + $serial) * ($x + 10) / 100 % 10 - 5;
        }
    }
    // Initialize summed-area table
    // https://en.wikipedia.org/wiki/Summed-area_table
    foreach (range(1, 300) as $y) {
        foreach (range(1, 300) as $x) {
            $summed[$y][$x] = $summed[$y - 1][$x - 1] ?? 0;
            if ($y > 1) foreach (range(1, $y - 1) as $y2) {
                $summed[$y][$x] += $cells[$y2][$x] ?? 0;
            }
            if ($x > 1) foreach (range(1, $x - 1) as $x2) {
                $summed[$y][$x] += $cells[$y][$x2] ?? 0;
            }
            $summed[$y][$x] += $cells[$y][$x];
        }
    }
    // Search for the best power cells
    $best = [];
    foreach (range(1, 300) as $size) {
        foreach (range(1, 300 - $size + 1) as $y) {
            foreach (range(1, 300 - $size + 1) as $x) {
                $score = $summed[$y + $size - 1][$x + $size - 1];
                $score += $summed[$y - 1][$x - 1] ?? 0;
                $score -= $summed[$y - 1][$x + $size - 1] ?? 0;
                $score -= $summed[$y + $size - 1][$x - 1] ?? 0;
                if (!isset($best['score']) || $score > $best['score']) {
                    $best['score'] = $score;
                    $best['x'] = $x;
                    $best['y'] = $y;
                    $best['size'] = $size;
                }
            }
        }
    }
    return "{$best['x']},{$best['y']},{$best['size']}";
};

echo $resolve(18), "\n"; // 90,269,16
echo $resolve(42), "\n"; // 232,251,12

echo $resolve(1308), "\n"; // 227,199,19
