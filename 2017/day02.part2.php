<?php

$resolve = function (array $spreadsheet) : int {
    return array_reduce($spreadsheet, function (int $sum, array $line) : int {
        foreach ($line as $op1) {
            foreach ($line as $op2) {
                if ($op1 !== $op2 && $op1 % $op2 === 0) {
                    return $sum + ($op1 / $op2);
                }
            }
        }
    }, 0);
};

echo $resolve([
    [5, 9, 2, 8],
    [9, 4, 7, 3],
    [3, 8, 6, 5],
]), "\n";

echo $resolve(
    array_map(
        function ($line) {
            return preg_split('/\s+/', $line);
        },
        file(__DIR__ . '/day02.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    )
), "\n";
