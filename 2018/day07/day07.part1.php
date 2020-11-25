<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $steps) : string {
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
    // Initialize the available list to the first steps to handle
    $available = array_filter($steps, function (string $step) use ($parents) {
        return !isset($parents[$step]);
    });
    // Iterate over steps
    $done = [];
    do {
        // Sort available list
        $available = array_usort($available, function (string $step1, string $step2) {
            return $step1 <=> $step2;
        });
        // Mark current step as done
        $done[] = $step = array_shift($available);
        // Add the next available steps to the stack
        if (isset($children[$step] )) {
            foreach ($children[$step] as $child) {
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
    } while (count($available) !== 0);
    // Return the step list
    return glue($done);
};

echo $resolve([
    'Step C must be finished before step A can begin.',
    'Step C must be finished before step F can begin.',
    'Step A must be finished before step B can begin.',
    'Step A must be finished before step D can begin.',
    'Step B must be finished before step E can begin.',
    'Step D must be finished before step E can begin.',
    'Step F must be finished before step E can begin.',
]), "\n"; // CABDFE

echo $resolve(
    file(__DIR__ . '/puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // CFGHAEMNBPRDISVWQUZJYTKLOX
