<?php

$coins = [2, 3, 5, 7, 9];

function comb($arr)
{
    $combs = [];

    if (count($arr) == 1) {
        return [[$arr[0]]];
    }

    foreach ($arr as $index => $item) {
        $copy = $arr;
        array_splice($copy, $index, 1);
        $subComb = comb($copy);

        foreach ($subComb as $c) {
            $combs[] = array_merge([$item], $c);
        }
    }

    return $combs;
}

$combinations = comb($coins);

foreach ($combinations as $c) {
    $result = $c[0] + $c[1] * $c[2] ** 2 + $c[3] ** 3 - $c[4];

    if ($result == 399) {
        print_r($c);
        exit;
    }
}