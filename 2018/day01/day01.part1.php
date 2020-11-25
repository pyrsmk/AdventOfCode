<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $frequencies) : int {
    return array_sum($frequencies);
};

echo $resolve(['+1', '+1', '+1']), "\n"; // 3
echo $resolve(['+1', '+1', '-2']), "\n"; // 0
echo $resolve(['-1', '-2', '-3']), "\n"; // -6

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 574
