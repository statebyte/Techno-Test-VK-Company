<?php
// try5.php (C) StateByte 2025 Omar El Said VK Techno Test
function parseInput(): array
{
    $input = file_get_contents("php://stdin");
    $lines = explode("\n", trim($input));

    if (count($lines) < 3) {
        fwrite(STDERR, "Invalid input: insufficient parameters.\n");
        exit(1);
    }

    $size = array_map('intval', explode(' ', array_shift($lines)));
    if (count($size) !== 2) {
        fwrite(STDERR, "Invalid input: incorrect maze size format.\n");
        exit(1);
    }

    [$rows, $cols] = $size;

    $maze = [];
    for ($i = 0; $i < $rows; $i++) {
        if (!isset($lines[$i])) {
            fwrite(STDERR, "Invalid input: maze rows do not match specified size.\n");
            exit(1);
        }

        $mazeRow = array_map('intval', explode(' ', $lines[$i]));
        if (count($mazeRow) !== $cols) {
            fwrite(STDERR, "Invalid input: inconsistent row size in maze.\n");
            exit(1);
        }
        $maze[] = $mazeRow;
    }

    $coordinates = array_map('intval', explode(' ', $lines[$rows]));
    if (count($coordinates) !== 4) {
        fwrite(STDERR, "Invalid input: incorrect format for start and finish coordinates.\n");
        exit(1);
    }

    $start = [$coordinates[0], $coordinates[1]];
    $finish = [$coordinates[2], $coordinates[3]];

    return [$rows, $cols, $maze, $start, $finish];
}

function findShortestPath(int $rows, int $cols, array $maze, array $start, array $finish): array
{
    $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]];
    $dist = array_fill(0, $rows, array_fill(0, $cols, PHP_INT_MAX));
    $prev = array_fill(0, $rows, array_fill(0, $cols, null));

    $dist[$start[0]][$start[1]] = 0;
    $queue = new SplPriorityQueue();
    $queue->insert($start, 0);

    while (!$queue->isEmpty()) {
        [$curRow, $curCol] = $queue->extract();

        foreach ($directions as [$dRow, $dCol]) {
            $newRow = $curRow + $dRow;
            $newCol = $curCol + $dCol;

            if ($newRow >= 0 && $newRow < $rows && $newCol >= 0 && $newCol < $cols && $maze[$newRow][$newCol] > 0) {
                $newDist = $dist[$curRow][$curCol] + $maze[$newRow][$newCol];
                if ($newDist < $dist[$newRow][$newCol]) {
                    $dist[$newRow][$newCol] = $newDist;
                    $prev[$newRow][$newCol] = [$curRow, $curCol];
                    $queue->insert([$newRow, $newCol], -$newDist);
                }
            }
        }
    }

    if ($dist[$finish[0]][$finish[1]] === PHP_INT_MAX) {
        fwrite(STDERR, "No path found between start and finish.\n");
        exit(1);
    }

    $path = [];
    for ($pos = $finish; $pos !== null; $pos = $prev[$pos[0]][$pos[1]]) {
        array_unshift($path, $pos);
    }

    return $path;
}

function main(): void
{
    try {
        [$rows, $cols, $maze, $start, $finish] = parseInput();
        $path = findShortestPath($rows, $cols, $maze, $start, $finish);

        foreach ($path as $coords) {
            echo implode(' ', $coords) . "\n";
        }
        echo ".\n";
    } catch (Exception $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        exit(1);
    }
}

main();