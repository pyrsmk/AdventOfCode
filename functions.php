<?php

function array_kvreduce(array $array, callable $callback, $initial = null) {
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

function array_diff_strict(array $array1, array $array2) : array {
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

function array_intersect_strict(array $array1, array $array2) : array {
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

function array_fill_multi(int $dimensions, int $size, $value) : array {
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

function glue(array $array) : string {
    return implode('', $array);
}

function is_even(int $value) : bool {
    return $value % 2 === 0;
}

function is_odd(int $value) : bool {
    return $value % 2 !== 0;
}
