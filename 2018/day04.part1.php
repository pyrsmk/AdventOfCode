<?php

require __DIR__ . '/../functions.php';

$resolve = function (array $logs) : int {
    $guards = array_uasort(
        array_reduce(
            array_usort($logs, function (string $log1, string $log2) {
                return strtotime(regex_match('/\[(.+?)\]/', $log1)[1])
                    <=> strtotime(regex_match('/\[(.+?)\]/', $log2)[1]);
            }),
            function (array $naps, string $log) : array {
                [1 => $minute, 2 => $event] = regex_match('/\[\d{4}-\d{2}-\d{2} \d{2}:(\d{2})\] (.+)/', $log);
                $words = explode(' ', $event);
                switch ($words[0]) {
                    case 'Guard':
                        $id = (int) substr($words[1], 1);
                        if (!isset($naps[$id])) $naps[$id] = [];
                        seek($naps, $id);
                        break;
                    case 'falls':
                        $id = key($naps);
                        $min = (int) $minute;
                        if (!isset($naps[$id][$min])) $naps[$id][$min] = 0;
                        ++$naps[$id][$min];
                        seek($naps[$id], $min);
                        break;
                    case 'wakes':
                        $id = key($naps);
                        foreach (range(key($naps[$id]) + 1, (int) $minute - 1) as $min) {
                            if (!isset($naps[$id][$min])) $naps[$id][$min] = 0;
                            ++$naps[$id][$min];
                        }
                        break;

                }
                return $naps;
            },
            []
        ),
        function (array $guard1, array $guard2) : int {
            return array_sum($guard1) < array_sum($guard2);
        }
    );
    return key($guards) * kmax($guards[key($guards)]);
};

echo $resolve([
    '[1518-11-01 00:00] Guard #10 begins shift',
    '[1518-11-01 00:05] falls asleep',
    '[1518-11-01 00:25] wakes up',
    '[1518-11-01 00:30] falls asleep',
    '[1518-11-01 00:55] wakes up',
    '[1518-11-01 23:58] Guard #99 begins shift',
    '[1518-11-02 00:40] falls asleep',
    '[1518-11-02 00:50] wakes up',
    '[1518-11-03 00:05] Guard #10 begins shift',
    '[1518-11-03 00:24] falls asleep',
    '[1518-11-03 00:29] wakes up',
    '[1518-11-04 00:02] Guard #99 begins shift',
    '[1518-11-04 00:36] falls asleep',
    '[1518-11-04 00:46] wakes up',
    '[1518-11-05 00:03] Guard #99 begins shift',
    '[1518-11-05 00:45] falls asleep',
    '[1518-11-05 00:55] wakes up',
]), "\n"; // 240

echo $resolve(
    file(__DIR__ . '/day04.puzzle.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
), "\n"; // 39698
