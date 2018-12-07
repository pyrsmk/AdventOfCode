<?php

require __DIR__ . '/../functions.php';

$resolve = function (string $polymer) : int {
    $polymer = str_split($polymer);
    while (true) {
        $reduced = [];
        for ($i = 0, $j = count($polymer); $i < $j; ++$i) {
            if ($i < $j -1
                && strcasecmp($polymer[$i], $polymer[$i + 1]) === 0
                && strcmp($polymer[$i], $polymer[$i + 1]) !== 0
            ) {
                ++$i;
                continue;
            }
            $reduced[] = $polymer[$i];
        }
        if (count($reduced) === count($polymer)) {
            break;
        }
        $polymer = $reduced;
    }
    return count($polymer);
};

echo $resolve('dabAcCaCBAcCcaDA'), "\n"; // 10

echo $resolve(
    trim(
        file_get_contents(__DIR__ . '/day05.puzzle.txt')
    )
), "\n"; // 10804
