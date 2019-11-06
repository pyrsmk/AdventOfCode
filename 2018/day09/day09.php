<?php

require __DIR__ . '/../functions.php';

// Too many nested objects for GC
gc_disable();

// Our small double-linked list data structure
final class Marble
{
    public function __construct(int $id, ?Marble $left = null, ?Marble $right = null)
    {
        $this->id = $id;
        $this->left = $left ?? $this;
        $this->right = $right ?? $this;
    }
}

$resolve = function (string $data) : int {
    // Extract data
    $matches = regex_match('/^(\d+) players; last marble is worth (\d+) points$/', $data);
    $players = (int) $matches[1];
    $last_marble = (int) $matches[2];
    // Initialize game
    $marble = new Marble(0);
    $scores = array_fill_multi(1, $players, 0);
    // Place marbles
    foreach (range(1, $last_marble) as $id) {
        if ($id % 23 !== 0) {
            $marble = $marble->right->right;
            $marble = new Marble($id, $marble->left, $marble);
            $marble->left->right = $marble;
            $marble->right->left = $marble;
        } else {
            $marble = $marble->left->left->left->left->left->left->left;
            $scores[$id % $players] += $id + $marble->id;
            $marble->left->right = $marble->right;
            $marble->right->left = $marble->left;
            $marble = $marble->right;
        }
    }
    // Return the highest score
    return max($scores);
};

echo $resolve('9 players; last marble is worth 25 points'), "\n"; // 32
echo $resolve('10 players; last marble is worth 1618 points'), "\n"; // 8317
echo $resolve('13 players; last marble is worth 7999 points'), "\n"; // 146373
echo $resolve('17 players; last marble is worth 1104 points'), "\n"; // 2764
echo $resolve('21 players; last marble is worth 6111 points'), "\n"; // 54718
echo $resolve('30 players; last marble is worth 5807 points'), "\n"; // 37305

echo $resolve('419 players; last marble is worth 72164 points'), "\n"; // 423717
echo $resolve('419 players; last marble is worth 7216400 points'), "\n"; // 3553108197
