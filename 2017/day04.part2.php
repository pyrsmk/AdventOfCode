<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $passphrases) : int {
    return array_reduce($passphrases, function (int $count, string $passphrase) : int {
        $words = explode(' ', $passphrase);
        foreach ($words as $word1) {
            if (count(array_keys($words, $word1)) > 1) {
                return $count;
            }
            foreach ($words as $word2) {
                if ($word1 !== $word2) {
                    if (array_sort(str_split($word1)) === array_sort(str_split($word2))) {
                        return $count;
                    }
                }
            }
        }
        return $count + 1;
    }, 0);
};

echo $resolve([
    'abcde fghij',
    'abcde xyz ecdab',
    'a ab abc abd abf abj',
    'iiii oiii ooii oooi oooo',
    'oiii ioii iioi iiio',
]), "\n"; // 3

echo $resolve(
    file(__DIR__ . '/day04.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 381
