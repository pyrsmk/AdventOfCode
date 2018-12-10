<?php

/**
 * Drop part of an array
 *
 * @param array $array
 * @param integer $offset
 * @param integer $length
 * @return array
 */
function array_drop(array $array, int $offset, int $length) : array
{
    array_splice($array, $offset, $length);
    return $array;
}

/**
 * Improved array_splice() with full string keys support when replacing
 *
 * @param array $array
 * @param integer $offset
 * @param integer $length
 * @param array $replacement
 * @return array
 */
function array_substitute(array $array, int $offset, int $length, array $replacement) : array
{
    return array_merge(
        array_slice($array, 0, $offset),
        $replacement,
        array_slice($array, $offset + $length)
    );
}

/**
 * array_reduce with key/value support
 *
 * @param array $array
 * @param callable $callback
 * @param mixed $initial
 * @return void
 */
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

/**
 * array_map with key/value support
 *
 * @param array $array
 * @param callable $callback
 * @return array
 */
function array_kvmap(array $array, callable $callback) : array
{
    return array_map($callback, array_keys($array), $array);
}

/**
 * Strict diff between two arrays by comparing the values at the same index
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
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

/**
 * Strict insersection between two arrays by comparing the values at the same index
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
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

/**
 * Initialize arrays with several dimensions
 *
 * @param integer $dimensions
 * @param integer $size
 * @param mixed $value
 * @return array
 */
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

/**
 * Immutable sort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_sort(array $array, int $flags = SORT_REGULAR) : array
{
    sort($array, $flags);
    return $array;
}

/**
 * Immutable asort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_asort(array $array, int $flags = SORT_REGULAR) : array
{
    asort($array, $flags);
    return $array;
}

/**
 * Immutable arsort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_arsort(array $array, int $flags = SORT_REGULAR) : array
{
    arsort($array, $flags);
    return $array;
}

/**
 * Immutable rsort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_rsort(array $array, int $flags = SORT_REGULAR) : array
{
    rsort($array, $flags);
    return $array;
}

/**
 * Immutable ksort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_ksort(array $array, int $flags = SORT_REGULAR) : array
{
    ksort($array, $flags);
    return $array;
}

/**
 * Immutable krsort()
 *
 * @param array $array
 * @param integer $flags
 * @return array
 */
function array_krsort(array $array, int $flags = SORT_REGULAR) : array
{
    krsort($array, $flags);
    return $array;
}

/**
 * Immutable usort()
 *
 * @param array $array
 * @param callable $compare
 * @return array
 */
function array_usort(array $array, callable $compare) : array
{
    usort($array, $compare);
    return $array;
}

/**
 * Immutable uksort()
 *
 * @param array $array
 * @param callable $compare
 * @return array
 */
function array_uksort(array $array, callable $compare) : array
{
    uksort($array, $compare);
    return $array;
}

/**
 * Immutable uasort()
 *
 * @param array $array
 * @param callable $compare
 * @return array
 */
function array_uasort(array $array, callable $compare) : array
{
    uasort($array, $compare);
    return $array;
}

/**
 * Immutable natsort()
 *
 * @param array $array
 * @return array
 */
function array_natsort(array $array) : array
{
    natsort($array);
    return $array;
}

/**
 * Immutable natcasesort()
 *
 * @param array $array
 * @return array
 */
function array_natcasesort(array $array) : array
{
    natcasesort($array);
    return $array;
}

/**
 * Move the array pointer
 *
 * @param array $array
 * @param integer|string $key
 * @return void
 */
function seek(array &$array, $key) : void
{
    reset($array);
    while(key($array) !== $key) {
        if (next($array) === false) {
            throw new Exception("'$key' key not found");
        }
    }
}

/**
 * Return the key of the minimum value
 *
 * @param array $array
 * @return void
 */
function kmin(array $array)
{
    return array_search(
        min($array),
        $array
    );
}

/**
 * Return the key of the maximum value
 *
 * @param array $array
 * @return void
 */
function kmax(array $array)
{
    return array_search(
        max($array),
        $array
    );
}

/**
 * Glue array elements
 *
 * @param array $array
 * @return string
 */
function glue(array $array) : string
{
    return implode('', $array);
}

/**
 * Verify if the value is even
 *
 * @param integer $value
 * @return boolean
 */
function is_even(int $value) : bool
{
    return $value % 2 === 0;
}

/**
 * Verify if the value is odd
 *
 * @param integer $value
 * @return boolean
 */
function is_odd(int $value) : bool
{
    return $value % 2 !== 0;
}

/**
 * Test if a regex matches against a string
 *
 * @param string $pattern
 * @param string $text
 * @param integer $flags
 * @return boolean
 */
function regex_test(string $pattern, string $text, int $flags = 0) : bool
{
    return (bool) preg_match($pattern, $text, $matches, $flags);
}

/**
 * Count the number of matches for a regex in a string
 *
 * @param string $pattern
 * @param string $text
 * @param integer $flags
 * @return integer
 */
function regex_count(string $pattern, string $text, int $flags = 0) : int
{
    return (int) preg_match($pattern, $text, $matches, $flags);
}

/**
 * Return the matches of a regex, for the first match
 *
 * @param string $pattern
 * @param string $text
 * @param integer $flags
 * @return array
 */
function regex_match(string $pattern, string $text, int $flags = 0) : array
{
    if (!preg_match($pattern, $text, $matches, $flags)) {
        throw new Exception("'$pattern' has no match");
    }
    return $matches;
}

/**
 * Return all the matches of a regex
 *
 * @param string $pattern
 * @param string $text
 * @param integer $flags
 * @return array
 */
function regex_match_all(string $pattern, string $text, int $flags = 0) : array
{
    if (!preg_match_all($pattern, $text, $matches, $flags)) {
        throw new Exception("'$pattern' has no match");
    }
    return $matches;
}
