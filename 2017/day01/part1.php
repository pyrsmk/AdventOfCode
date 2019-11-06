<?php

require __DIR__ . '/../functions.php';

$resolve = function (string $captcha) : int {
    $sum = 0;
    foreach (str_split($captcha) as $i => $digit) {
        if ($digit == $captcha[($i + 1) % strlen($captcha)]) {
            $sum += $digit;
        }
    }
    return $sum;
};

echo $resolve('1122'), "\n"; // 3
echo $resolve('1111'), "\n"; // 4
echo $resolve('1234'), "\n"; // 0
echo $resolve('91212129'), "\n"; // 9

echo $resolve(
    trim(file_get_contents(__DIR__ . '/puzzle.txt'))
), "\n"; // 1341
