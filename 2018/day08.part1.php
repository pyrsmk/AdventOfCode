<?php

require __DIR__ . '/../functions.php';

$resolve = function (string $data) : int {
    // Format data
    $load = function (string $data) : array {
        return array_kvmap(explode(' ', $data), function (int $i, string $number) : int {
            return (int) $number;
        });
    };
    // Convert data to nodes
    $parse = function (array $data) : array {
        $nodes = [];
        $mode = 'READ_HEADER';
        $i = -1;
        // Loop on data
        while (isset($data[++$i])) {
            switch ($mode) {
                case 'READ_HEADER':
                    // Read the header, create a new node and add it to the stack
                    $nodes[] = [
                        'header' => [
                            'children' => (int) $data[$i],
                            'metadata' => (int) $data[$i + 1],
                        ],
                        'children' => [],
                        'metadata' => [],
                    ];
                    end($nodes);
                    // Next iteration: read metadata and close the open node
                    if (current($nodes)['header']['children'] === 0) {
                        $mode = 'READ_METADATA';
                    }
                    // Move index
                    $i += 1;
                    break;
                case 'READ_METADATA':
                    // Extract metadata
                    $nodes[key($nodes)]['metadata'] = array_filter(
                        array_slice($data, $i, current($nodes)['header']['metadata']),
                        function (string $metadata) : int {
                            return (int) $metadata;
                        }
                    );
                    // Move index
                    $i += current($nodes)['header']['metadata'] - 1;
                    // Move node to previous node's children (unless we're at the root)
                    $child_key = key($nodes);
                    prev($nodes);
                    if (key($nodes) !== null) {
                        $nodes[key($nodes)]['children'][] = $nodes[$child_key];
                        $nodes = array_drop($nodes, $child_key);
                        end($nodes);
                        // If there's still children to load, change mode
                        if ($nodes[key($nodes)]['header']['children'] !== count($nodes[key($nodes)]['children'])) {
                            $mode = 'READ_HEADER';
                        }
                    }
                    break;
            }
        }
        return $nodes[0];
    };
    // Compute CRC based on nodes' metadata
    $crc = function (array $node) use (&$crc) : int {
        return array_sum($node['metadata']) + array_kvreduce(
            $node['children'],
            function (int $sum, int $i, array $child) use ($crc) {
                return $sum + $crc($child);
            },
            0
        );
    };
    return $crc(
        $parse(
            $load($data)
        )
    );
};

echo $resolve('2 3 0 3 10 11 12 1 1 0 1 99 2 1 1 2'), "\n"; // 138

echo $resolve(
    trim(
        file_get_contents(__DIR__ . '/day08.puzzle.txt')
    )
), "\n"; // 40977
