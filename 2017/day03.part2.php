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
    $direction = 'right';
    // Write first square
    $memory[$y][$x++] = 1;
    // Fill the memory up
    if ($area > 1) {
        foreach (range(2, $area) as $value) {
            // Save square
            $memory[$y][$x] = ($memory[$y][$x + 1] ?? 0)
                + ($memory[$y - 1][$x + 1] ?? 0)
                + ($memory[$y - 1][$x] ?? 0)
                + ($memory[$y - 1][$x - 1] ?? 0)
                + ($memory[$y][$x - 1] ?? 0)
                + ($memory[$y + 1][$x - 1] ?? 0)
                + ($memory[$y + 1][$x] ?? 0)
                + ($memory[$y + 1][$x + 1] ?? 0);
            if ($memory[$y][$x] > $square) {
                return $memory[$y][$x];
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
};

echo $resolve(12), "\n"; // 23
echo $resolve(23), "\n"; // 25
echo $resolve(747), "\n"; // 806

echo $resolve(325489), "\n"; // 330785
