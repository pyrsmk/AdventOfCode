<?php

$resolve = function (array $spreadsheet) : int {
    return array_reduce($spreadsheet, function (int $sum, array $line) : int {
        return $sum + (max($line) - min($line));
    }, 0);
};

echo $resolve([
    [5, 1, 9, 5],
    [7, 5, 3],
    [2, 4, 6, 8],
]), "\n"; // 18

echo $resolve(
    array_map(
        function ($line) {
            return preg_split('/\s+/', $line);
        },
        file(__DIR__ . '/day02.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    )
), "\n"; // 36766
