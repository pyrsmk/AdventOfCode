<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $ids) : string {
    foreach ($ids as $i1 => $id1) {
        foreach ($ids as $i2 => $id2) {
            if (count(array_diff_strict(str_split($id1), str_split($id2))) === 1) {
                return glue(array_intersect_strict(str_split($id1), str_split($id2)));
            }
        }
    }
    return '';
};

echo $resolve([
    'abcde',
    'fghij',
    'klmno',
    'pqrst',
    'fguij',
    'axcye',
    'wvxyz',
]), "\n";

echo $resolve(
    file(__DIR__ . '/day02.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n";
