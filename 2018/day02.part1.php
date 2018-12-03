<?php

$resolve = function (array $ids) : int {
    $count = function (int $number) use ($ids) : int {
        return array_reduce($ids, function (int $count, string $id) use ($number) : int {
            foreach (str_split($id) as $letter) {
                if (substr_count($id, $letter) === $number) {
                    return $count + 1;
                }
            }
            return $count;
        }, 0);
    };
    return $count(2) * $count(3);
};

echo $resolve([
    'abcdef',
    'bababc',
    'abbcde',
    'abcccd',
    'aabcdd',
    'abcdee',
    'ababab',
]), "\n"; // 12

echo $resolve(
    file(__DIR__ . '/day02.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 4940
