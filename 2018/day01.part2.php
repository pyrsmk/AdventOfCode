<?php

$resolve = function (array $frequencies) : int {
    $reached = [$sum = 0];
    while (true) {
        foreach ($frequencies as $frequency) {
            $sum += $frequency;
            if (in_array($sum, $reached)) {
                return $sum;
            }
            $reached[] = $sum;
        }
    }
};

echo $resolve(['+1', '-1']), "\n"; // 0
echo $resolve(['+3', '+3', '+4', '-2', '-4']), "\n"; // 10
echo $resolve(['-6', '+3', '+8', '+5', '-6']), "\n"; // 5
echo $resolve(['+7', '+7', '-2', '-7', '-4']), "\n"; // 14

echo $resolve(
    file(__DIR__ . '/day01.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 452
