<?php

$resolve = function (array $frequencies) : int {
    return array_reduce($frequencies, function ($sum, $frequency) {
        return $sum += $frequency;
    }, 0);
};

echo $resolve(['+1', '+1', '+1']), "\n";
echo $resolve(['+1', '+1', '-2']), "\n";
echo $resolve(['-1', '-2', '-3']), "\n";
echo $resolve(file('day01.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), "\n";
