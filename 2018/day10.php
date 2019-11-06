<?php

require __DIR__ . '/../functions.php';

final class Velocity
{
    private $x;
    private $y;
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
    public function x() : int
    {
        return $this->x;
    }
    public function y() : int
    {
        return $this->y;
    }
}

final class Point
{
    private $x;
    private $y;
    private $velocity;
    public function __construct(int $x, int $y, Velocity $velocity)
    {
        $this->x = $x;
        $this->y = $y;
        $this->velocity = $velocity;
    }
    public function x() : int
    {
        return $this->x;
    }
    public function y() : int
    {
        return $this->y;
    }
    public function velocity() : int
    {
        return $this->velocity;
    }
    public function move() : void
    {
        $this->x += $this->velocity->x();
        $this->y += $this->velocity->y();
    }
    public function adjacent(Map $map) : array
    {
        return array_kvreduce(
            [
                ['x' => $this->x, 'y' => $this->y - 1],
                ['x' => $this->x + 1, 'y' => $this->y - 1],
                ['x' => $this->x + 1, 'y' => $this->y],
                ['x' => $this->x + 1, 'y' => $this->y + 1],
                ['x' => $this->x, 'y' => $this->y + 1],
                ['x' => $this->x - 1, 'y' => $this->y + 1],
                ['x' => $this->x - 1, 'y' => $this->y],
                ['x' => $this->x - 1, 'y' => $this->y - 1],
            ],
            function (array $points, int $i, array $position) use ($map) {
                if ($map->exists($position['x'], $position['y'])) {
                    $points[] = $map->point($position['x'], $position['y']);
                }
                return $points;
            },
            []
        );
    }
}

final class Map
{
    private $points = [];
    public function points() : array
    {
        return $this->points;
    }
    public function add(Point $point) : void
    {
        $this->points[] = $point;
    }
    public function point(int $x, int $y) : Point
    {
        foreach ($this->points as $point) {
            if ($point->x() === $x && $point->y() === $y) {
                return $point;
            }
        }
        throw new Exception();
    }
    public function exists(int $x, int $y) : bool
    {
        try {
            $this->point($x, $y);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public function move() : void
    {
        foreach ($this->points as $point) {
            $point->move();
        }
    }
    public function resolved() : bool
    {
        foreach ($this->points as $point) {
            if (count($point->adjacent($this)) === 0) {
                return false;
            }
        }
        return true;
    }
}

final class Message
{
    private $map;
    public function __construct(Map $map)
    {
        $this->map = $map;
    }
    public function map() : Map
    {
        return $this->map;
    }
    public function write() : void
    {
        // Find the endpoints
        $endpoints = array_kvreduce($this->map->points(), function (array $endpoints, string $reference, Point $point) {
            if (!isset($endpoints['min_x']) || $point->x() < $endpoints['min_x']) {
                $endpoints['min_x'] = $point->x();
            }
            if (!isset($endpoints['max_x']) || $point->x() > $endpoints['max_x']) {
                $endpoints['max_x'] = $point->x();
            }
            if (!isset($endpoints['min_y']) || $point->y() < $endpoints['min_y']) {
                $endpoints['min_y'] = $point->y();
            }
            if (!isset($endpoints['max_y']) || $point->y() > $endpoints['max_y']) {
                $endpoints['max_y'] = $point->y();
            }
            return $endpoints;
        }, []);
        // Write the message
        foreach (range($endpoints['min_y'], $endpoints['max_y']) as $y) {
            foreach (range($endpoints['min_x'], $endpoints['max_x']) as $x) {
                if ($this->map->exists($x, $y)) {
                    echo '█';
                } else {
                    echo ' ';
                }
            }
            echo "\n";
        }
    }
}

$resolve = function (array $data) : void {
    // Load points on the map
    $map = array_kvreduce(
        $data,
        function (Map $map, int $i, string $data) {
            [
                1 => $pos_x,
                2 => $pos_y,
                3 => $vel_x,
                4 => $vel_y,
            ] = regex_match('/^position=<\s*([0-9-]+),\s*([0-9-]+)> velocity=<\s*([0-9-]+),\s*([0-9-]+)>$/', $data);
            $map->add(
                new Point(
                    (int) $pos_x,
                    (int)  $pos_y,
                    new Velocity(
                        (int) $vel_x,
                        (int) $vel_y
                    )
                )
            );
            return $map;
        },
        new Map()
    );
    // Move points
    $seconds = 0;
    while (!$map->resolved()) {
        $map->move();
        $seconds += 1;
    }
    // Read message
    echo "At $seconds seconds, this message would appear :\n\n";
    (new Message($map))->write();
    echo "\n\n";
};

$resolve([
    'position=< 9,  1> velocity=< 0,  2>',
    'position=< 7,  0> velocity=<-1,  0>',
    'position=< 3, -2> velocity=<-1,  1>',
    'position=< 6, 10> velocity=<-2, -1>',
    'position=< 2, -4> velocity=< 2,  2>',
    'position=<-6, 10> velocity=< 2, -2>',
    'position=< 1,  8> velocity=< 1, -1>',
    'position=< 1,  7> velocity=< 1,  0>',
    'position=<-3, 11> velocity=< 1, -2>',
    'position=< 7,  6> velocity=<-1, -1>',
    'position=<-2,  3> velocity=< 1,  0>',
    'position=<-4,  3> velocity=< 2,  0>',
    'position=<10, -3> velocity=<-1,  1>',
    'position=< 5, 11> velocity=< 1, -2>',
    'position=< 4,  7> velocity=< 0, -1>',
    'position=< 8, -2> velocity=< 0,  1>',
    'position=<15,  0> velocity=<-2,  0>',
    'position=< 1,  6> velocity=< 1,  0>',
    'position=< 8,  9> velocity=< 0, -1>',
    'position=< 3,  3> velocity=<-1,  1>',
    'position=< 0,  5> velocity=< 0, -1>',
    'position=<-2,  2> velocity=< 2,  0>',
    'position=< 5, -2> velocity=< 1,  2>',
    'position=< 1,  4> velocity=< 2,  1>',
    'position=<-2,  7> velocity=< 2, -2>',
    'position=< 3,  6> velocity=<-1, -1>',
    'position=< 5,  0> velocity=< 1,  0>',
    'position=<-6,  0> velocity=< 2,  0>',
    'position=< 5,  9> velocity=< 1, -2>',
    'position=<14,  7> velocity=<-2,  0>',
    'position=<-3,  6> velocity=< 2, -1>',
]); // HI

$resolve(
    file(__DIR__ . '/day10.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
); // LRCXFXRP
