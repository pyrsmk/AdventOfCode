<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $passphrases) : int {
    return array_reduce($passphrases, function (int $count, string $passphrase) : int {
        $words = explode(' ', $passphrase);
        foreach ($words as $word) {
            if (count(array_keys($words, $word)) > 1) {
                return $count;
            }
        }
        return $count + 1;
    }, 0);
};

echo $resolve([
    'aa bb cc dd ee',
    'aa bb cc dd aa',
    'aa bb cc dd aaa',
]), "\n"; // 2

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 477
