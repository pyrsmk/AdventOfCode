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

function glue(array $array) : string {
    return implode('', $array);
}
