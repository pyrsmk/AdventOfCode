<?php

function array_drop(array $array, int $offset, int $length) : array
{
    array_splice($array, $offset, $length);
    return $array;
}

function array_kvreduce(array $array, callable $callback, $initial = null)
{
    $carry = $initial;
    foreach ($array as $key => $value) {
        $carry = call_user_func_array($callback, [
            $carry,
            $key,
            $value
        ]);
    }
    return $carry;
}

function array_kvmap(array $array, callable $callback) : array
{
    return array_map($callback, array_keys($array), $array);
}

function array_diff_strict(array $array1, array $array2) : array
{
    if (count($array1) !== count($array2)) {
        throw new Exception('Arrays must be of the same length');
    }
    return array_kvreduce($array1, function (array $diff, $key, $value) use ($array2) : array {
        if ($value !== $array2[$key]) {
            $diff[] = [
                'offset' => $key,
                'left' => $value,
                'right' => $array2[$key],
            ];
        }
        return $diff;
    }, []);
}

function array_intersect_strict(array $array1, array $array2) : array
{
    if (count($array1) !== count($array2)) {
        throw new Exception('Arrays must be of the same length');
    }
    return array_kvreduce($array1, function (array $diff, $key, $value) use ($array2) : array {
        if ($value === $array2[$key]) {
            $diff[] = $value;
        }
        return $diff;
    }, []);
}

function array_fill_multi(int $dimensions, int $size, $value) : array
{
    $create = function (int $dimension, int $size, $value) use (&$create, $dimensions) : array {
        $array = [];
        foreach (range(1, $size) as $i) {
            if ($dimension < $dimensions) {
                $array[] = $create($dimension + 1, $size, $value);
            } else {
                $array[] = $value;
            }
        }
        return $array;
    };
    return $create(1, $size, $value);
}

function array_sort(array $array, int $flags = SORT_REGULAR) : array
{
    sort($array, $flags);
    return $array;
}

function array_asort(array $array, int $flags = SORT_REGULAR) : array
{
    asort($array, $flags);
    return $array;
}

function array_arsort(array $array, int $flags = SORT_REGULAR) : array
{
    arsort($array, $flags);
    return $array;
}

function array_rsort(array $array, int $flags = SORT_REGULAR) : array
{
    rsort($array, $flags);
    return $array;
}

function array_ksort(array $array, int $flags = SORT_REGULAR) : array
{
    ksort($array, $flags);
    return $array;
}

function array_krsort(array $array, int $flags = SORT_REGULAR) : array
{
    krsort($array, $flags);
    return $array;
}

function array_usort(array $array, callable $compare) : array
{
    usort($array, $compare);
    return $array;
}

function array_uksort(array $array, callable $compare) : array
{
    uksort($array, $compare);
    return $array;
}

function array_uasort(array $array, callable $compare) : array
{
    uasort($array, $compare);
    return $array;
}

function array_natsort(array $array) : array
{
    natsort($array);
    return $array;
}

function array_natcasesort(array $array) : array
{
    natcasesort($array);
    return $array;
}

function seek(array &$array, $key) : void
{
    reset($array);
    while(key($array) !== $key) {
        if (next($array) === false) {
            throw new Exception("'$key' key not found");
        }
    }
}

function kmin($array)
{
    return array_search(
        min($array),
        $array
    );
}

function kmax($array)
{
    return array_search(
        max($array),
        $array
    );
}

function glue(array $array) : string
{
    return implode('', $array);
}

function is_even(int $value) : bool
{
    return $value % 2 === 0;
}

function is_odd(int $value) : bool
{
    return $value % 2 !== 0;
}

function regex_test(string $pattern, string $text, int $flags = 0) : bool
{
    return (bool) preg_match($pattern, $text, $matches, $flags);
}

function regex_count(string $pattern, string $text, int $flags = 0) : int
{
    return (int) preg_match($pattern, $text, $matches, $flags);
}

function regex_match(string $pattern, string $text, int $flags = 0) : array
{
    if (!preg_match($pattern, $text, $matches, $flags)) {
        throw new Exception("'$pattern' has no match");
    }
    return $matches;
}

function match_all(string $pattern, string $text, int $flags = 0) : array
{
    if (!preg_match_all($pattern, $text, $matches, $flags)) {
        throw new Exception("'$pattern' has no match");
    }
    return $matches;
}
