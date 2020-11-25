<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $steps, int $workers_number, int $time_modifier) : int {
    // Initialize durations for each step
    $durations = [
        'A' => 1,   'B' => 2,   'C' => 3,   'D' => 4,   'E' => 5,   'F' => 6,
        'G' => 7,   'H' => 8,   'I' => 9,   'J' => 10,  'K' => 11,  'L' => 12,
        'M' => 13,  'N' => 14,  'O' => 15,  'P' => 16,  'Q' => 17,  'R' => 18,
        'S' => 19,  'T' => 20,  'U' => 21,  'V' => 22,  'W' => 23,  'X' => 24,
        'Y' => 25,  'Z' => 26,
    ];
    // Create list of reversed dependencies (parent => child)
    $children = array_kvreduce(
        $steps,
        function (array $rdependencies, int $i, string $step) {
            [
                1 => $before,
                2 => $after,
            ] = regex_match('/Step ([A-Z]) must be finished before step ([A-Z]) can begin./', $step);
            $rdependencies[$before][] = $after;
            return $rdependencies;
        },
        []
    );
    // Create list of dependencies (child => parent)
    $parents = array_kvreduce(
        $children,
        function (array $dependencies, string $parent, array $children) {
            foreach ($children as $child) {
                $dependencies[$child][] = $parent;
            }
            return $dependencies;
        },
        []
    );
    // Create list of all steps
    $steps = array_unique(
        array_merge(
            array_keys($parents),
            array_keys($children)
        )
    );
    // Initialize the available step list
    $available = array_filter($steps, function (string $step) use ($parents) {
        return !isset($parents[$step]);
    });
    // Create workers
    $workers = array_fill_multi(1, $workers_number, [
        'step' => null,
        'free' => null,
    ]);
    // Iterate over steps
    $done = [];
    $clock = -1;
    do {
        // Increment clock
        $clock += 1;
        // Close steps if they're finished
        foreach ($workers as &$data) {
            if (isset($data['free']) && $clock === $data['free']) {
                // Mark step as done
                $done[] = $data['step'];
                // Add the next available steps to the stack
                if (isset($children[$data['step']])) {
                    foreach ($children[$data['step']] as $child) {
                        if (isset($parents[$child])) {
                            foreach ($parents[$child] as $parent) {
                                if (!in_array($parent, $done)) {
                                    continue 2;
                                }
                            }
                        }
                        $available[] = $child;
                    }
                }
                // Sort available list
                $available = array_usort($available, function (string $step1, string $step2) {
                    return $step1 <=> $step2;
                });
                // Free worker
                $data['step'] = null;
                $data['free'] = null;
            }
        }
        // Find an available worker and give him the next step to handle
        if (count($available) > 0) {
            foreach ($workers as &$data) {
                if (count($available) > 0 && !isset($data['step'])) {
                    // Attrib a new step to the current worker
                    $data['step'] = array_shift($available);
                    $data['free'] = $clock + $durations[$data['step']] + $time_modifier;
                }
            }
        }
        // Loop until all steps have been executed
    } while (count($done) !== count($steps));
    // Return the duration
    return $clock;
};

echo $resolve([
    'Step C must be finished before step A can begin.',
    'Step C must be finished before step F can begin.',
    'Step A must be finished before step B can begin.',
    'Step A must be finished before step D can begin.',
    'Step B must be finished before step E can begin.',
    'Step D must be finished before step E can begin.',
    'Step F must be finished before step E can begin.',
], 2, 0), "\n"; // 15

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
    5,
    60
), "\n"; // 828
