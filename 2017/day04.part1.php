<?php

$resolve = function (array $passphrases) : int {
    return array_reduce($passphrases, function (int $valid, string $passphrase) : int {
        return $valid + (int) array_reduce(
            $phrases = explode(' ', $passphrase),
            function (bool $valid, string $phrase) use ($phrases) : bool {
                return $valid && count(array_keys($phrases, $phrase)) < 2;
            },
            true
        );
    }, 0);
};

echo $resolve([
    'aa bb cc dd ee',
    'aa bb cc dd aa',
    'aa bb cc dd aaa',
]), "\n"; // 2

echo $resolve(
    file(__DIR__ . '/day04.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 477
