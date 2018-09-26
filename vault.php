<?php

$rooms = [
    ['*' , '8', '-' , '1' ],
    ['4' , '*', '11', '*' ],
    ['+' , '4', '-' , '18'],
    [null, '-', '9' , '*' ],
];

$opposite = [
    'north' => 'south',
    'east' => 'west',
    'south' => 'north',
    'west' => 'east'
];

// Start row and column
$sRow = 3;
$sColumn = 0;

// Target row and column
$tRow = 0;
$tColumn = 3;
$tResult = 30;

// Queue contains [directions, row, column, value, result-until-now]
$queue = [[['north'], $sRow, $sColumn, '22', []]];

while (true) {
    $node = array_shift($queue);
    list ($directions, $row, $column, $value, $ops) = $node;

    $values = $ops;
    $values[] = $value;

    echo 'Went ' . implode(' ', $directions) . ' and got result: ' . calc($values) . ' [' . implode(', ', $values) . ']' . PHP_EOL;

    if ($row == $tRow && $column == $tColumn) {
        if (calc($values) == $tResult) {
            echo 'Found path: ' . implode(' ', $directions) . ' values: ' . ' [' . implode(', ', $values) . ']' . PHP_EOL;
            exit;
        }
        continue;
    }

    // If operation is not a number, don't go back to the last number to avoid 4*4*4*4*4*4*4*4 back to itself loops
    $forbid = ctype_digit($value) ? null : $opposite[end($directions)];

    // Try north
    $x = $row - 1;
    $y = $column;
    if ($forbid !== 'north' && isset($rooms[$x][$y])) {
        $dirs = $directions;
        $dirs[] = 'north';
        $queue[] = [$dirs, $x, $y, $rooms[$x][$y], $values];
    }

    // Try east
    $x = $row;
    $y = $column + 1;
    if ($forbid !== 'east' && isset($rooms[$x][$y])) {
        $dirs = $directions;
        $dirs[] = 'east';
        $queue[] = [$dirs, $x, $y, $rooms[$x][$y], $values];
    }

    // Try south
    $x = $row + 1;
    $y = $column;
    if ($forbid !== 'south' && isset($rooms[$x][$y])) {
        $dirs = $directions;
        $dirs[] = 'south';
        $queue[] = [$dirs, $x, $y, $rooms[$x][$y], $values];
    }

    // Try west
    $x = $row;
    $y = $column - 1;
    if ($forbid !== 'west' && isset($rooms[$x][$y])) {
        $dirs = $directions;
        $dirs[] = 'west';
        $queue[] = [$dirs, $x, $y, $rooms[$x][$y], $values];
    }
}

// Calculator
function calc($steps) {
    $result = array_shift($steps);
    $operation = null;

    foreach ($steps as $step) {
        if (ctype_digit($step)) {
            switch ($operation) {
                case '*': $result *= $step; break;
                case '-': $result -= $step; break;
                case '+': $result += $step; break;
            }
            continue;
        }

        $operation = $step;
    }

    return $result;
}