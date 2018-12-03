<?php

$resolve = function (string $captcha, int $v = 1) : int {
    $sum = 0;
    foreach (str_split($captcha) as $i => $digit) {
        if ($digit == $captcha[($i + (strlen($captcha) / 2)) % strlen($captcha)]) {
            $sum += $digit;
        }
    }
    return $sum;
};

echo $resolve('1212'), "\n"; // 6
echo $resolve('1221'), "\n"; // 0
echo $resolve('123425'), "\n"; // 4
echo $resolve('123123'), "\n"; // 12
echo $resolve('12131415'), "\n"; // 4

echo $resolve(
    trim(file_get_contents(__DIR__ . '/day01.puzzle.txt'))
), "\n"; // 1348
