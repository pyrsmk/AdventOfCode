<?php

require __DIR__ . '/../functions.php';

$resolve = function (int $square) : int {
    // Initialize memory space
    $area = $square;
    while (true) {
        $size = sqrt($area);
        if (intval($size) == $size) break;
        ++$area;
    }
    $memory = array_fill_multi(2, $size, null);
    // Initialize pointer to the first memory square
    if (is_even($size)) {
        $x = $size / 2 - 1;
        $y = $size - ($size / 2);
    } else {
        $x = $size / 2 + 0.5 - 1;
        $y = $size / 2 + 0.5 - 1;
    }
    $pointers[1] = [
        'x' => $x,
        'y' => $y,
    ];
    $direction = 'right';
    // Write first square
    $memory[$y][$x++] = 1;
    // Fill the memory up
    if ($area > 1) {
        foreach (range(2, $area) as $value) {
            // Save square
            $memory[$y][$x] = $value;
            if ($value === $square) {
                $pointers[$square] = [
                    'x' => $x,
                    'y' => $y,
                ];
            }
            // Change direction
            switch ($direction) {
                case 'right': if (!isset($memory[$y - 1][$x])) $direction = 'top'; break;
                case 'top': if (!isset($memory[$y][$x - 1])) $direction = 'left'; break;
                case 'left': if (!isset($memory[$y + 1][$x])) $direction = 'bottom'; break;
                case 'bottom': if (!isset($memory[$y][$x + 1])) $direction = 'right'; break;
            }
            // Increment position
            switch ($direction) {
                case 'right': ++$x; break;
                case 'top': --$y; break;
                case 'left': --$x; break;
                case 'bottom': ++$y; break;
            }
        }
    }
    // Compute how many moves we need
    return abs($pointers[1]['x'] - $pointers[$square]['x']) + abs($pointers[1]['y'] - $pointers[$square]['y']);
};

echo $resolve(1), "\n"; // 0
echo $resolve(12), "\n"; // 3
echo $resolve(23), "\n"; // 2
echo $resolve(1024), "\n"; // 31

echo $resolve(325489), "\n"; // 552
