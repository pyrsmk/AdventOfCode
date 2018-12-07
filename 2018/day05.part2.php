<?php

require __DIR__ . '/../functions.php';

$resolve = function (string $polymer) : int {
    $reduce = function (array $polymer) : int {
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
    $polymer = str_split($polymer);
    $results = [];
    foreach (array_unique(array_map('strtolower', $polymer)) as $remove_type) {
        $results[] = $reduce(
            array_values(
                array_filter($polymer, function (string $type) use ($remove_type) {
                    return strcasecmp($type, $remove_type) !== 0;
                })
            )
        );
    }
    return min($results);
};

echo $resolve('dabAcCaCBAcCcaDA'), "\n"; // 4

echo $resolve(
    trim(
        file_get_contents(__DIR__ . '/day05.puzzle.txt')
    )
), "\n"; // 6650
