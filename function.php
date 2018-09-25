<?php

/**
 * 32768..32775 instead mean registers 0..7
 *
 * 32768 -> 0
 * 32769 -> 1
 * 32770 -> 2
 * 32771 -> 3
 * 32772 -> 4
 * 32773 -> 5
 * 32774 -> 6
 * 32775 -> 7
 *
 */

$register = [
    0 => 4,
    1 => 1,
    2 => 3,
    3 => 10,
    4 => 101,
    5 => 0,
    6 => 0,
    7 => 1,
];

$stack = [
    6080,
    16,
    6124,
    1,
    2952,
    25978,
    3568,
    3599,
    2708,
    5445,
    3,
    5491,
];

$r0 = 4;
$r1 = 1;
$r7 = 1;

$time = microtime(true);
echo 'Starting with ' . $r0 . ' ' . $r1 . ' ' . $r7 . PHP_EOL;
$r0 = a5($r0, $r1, $r7);
echo 'Completed in ' . (microtime(true) - $time) . ' seconds' . PHP_EOL;
echo 'Register 0: ' . $r0 . PHP_EOL;
echo 'Register 1: ' . ($r0 - 1) . PHP_EOL;
echo 'Register 7: ' . $r7 . PHP_EOL;

// L5489 -> call(6027)

function a5($r0, $r1, $r7) {
    if ($r0 == 0) {
        return ($r1 + 1) % 32768;
    }

    if ($r1 == 0) {
        return a5($r0 - 1, $r7, $r7);
    }

    $r1 = a5($r0, $r1 - 1, $r7);

    return a5($r0 - 1, $r1, $r7);
}

function a4($r0, $r1, $r7) {
    if ($r0 == 0) {
        return [($r1 + 1) % 32768, $r1];
    }

    if ($r1 == 0) {
        return a4($r0 - 1, $r7, $r7);
    }

    [$r1,] = a4($r0, $r1 - 1, $r7);

    return a4($r0 - 1, $r1, $r7);
}

function a3($r0, $r1, $r7) {
    if ($r0 == 0) {
        return [($r1 + 1) % 32768, $r1];
    }

    if ($r1 == 0) {
        return a3($r0 - 1, $r7, $r7);
    }

    $save = $r0;

    [$r0, $r1] = a3($r0, $r1 - 1, $r7);

    return a3($save - 1, $r0, $r7);
}

function a2(&$r0, &$r1, $r7) {
    if ($r1 == 0) {                       // L6035 -> jt(32769, 6048)
        --$r0;                            // L6038 -> add(32768, 32768, 32767)
        $r1 = $r7;                        // L6042 -> set(32769, 32775)

        if ($r0 == 0) {                  // L6027 -> jt(32768, 6035)
            $r0 = $r1 + 1;               // L6030 -> add(32768, 32769, 1)
            return;                      // L6034 -> ret()
        }

        a2($r0, $r1, $r7);     // L6045 -> call(6027)
        return;                           // L6047 -> ret()
    }

    $save = $r0;                          // L6048 -> push(32768)
    --$r1;                                // L6050 -> add(32769, 32769, 32767)
    a2($r0, $r1, $r7);         // L6054 -> call(6027)

    $r1 = $r0;                            // L6056 -> set(32769, 32768)
    $r0 = $save - 1;                      // L6059 -> pop(32768) & L6061 -> add(32768, 32768, 32767)

    if ($r0 == 0) {                       // L6027 -> jt(32768, 6035)
        $r0 = ($r1 + 1) % 32768;          // L6030 -> add(32768, 32769, 1)
        return;                           // L6034 -> ret()
    }

    a2($r0, $r1, $r7);         // L6065 -> call(6027)
}

function a1() {
    global $register, $stack;

    if ($register[0] == 0) {                                // L6027 -> jt(32768, 6035)
        $register[0] = ($register[1] + 1) % 32768;          // L6030 -> add(32768, 32769, 1)
        return;                                             // L6034 -> ret()
    }

    if ($register[1] == 0) {                                // L6035 -> jt(32769, 6048)
        --$register[0];                                     // L6038 -> add(32768, 32768, 32767)
        $register[1] = $register[7];                        // L6042 -> set(32769, 32775)
        a1();                                               // L6045 -> call(6027)
        return;                                             // L6047 -> ret()
    }

    $stack[] = $register[0];                                // L6048 -> push(32768)
    --$register[1];                                         // L6050 -> add(32769, 32769, 32767)
    a1();                                                   // L6054 -> call(6027)

    $register[1] = $register[0];                            // L6056 -> set(32769, 32768)
    $register[0] = array_pop($stack);               // L6059 -> pop(32768)
    --$register[0];                                         // L6061 -> add(32768, 32768, 32767)
    a1();                                                   // L6065 -> call(6027)
    return;                                                 // L6067 -> ret()
}

function a() {
    global $register, $stack;

    if ($register[0] == 0) {                                // L6027 -> jt(32768, 6035)
        $register[0] = ($register[1] + 1) % 32768;          // L6030 -> add(32768, 32769, 1)
        return;                                             // L6034 -> ret()
    }

    if ($register[1] == 0) {                                // L6035 -> jt(32769, 6048)
        $register[0] = ($register[0] + 32767) % 32768;      // L6038 -> add(32768, 32768, 32767)
        $register[1] = $register[7];                        // L6042 -> set(32769, 32775)
        a();                                                // L6045 -> call(6027)
        return;                                             // L6047 -> ret()
    }

    $stack[] = $register[0];                                // L6048 -> push(32768)
    $register[1] = ($register[1] + 32767) % 32768;          // L6050 -> add(32769, 32769, 32767)
    a();                                                    // L6054 -> call(6027)

    $register[1] = $register[0];                            // L6056 -> set(32769, 32768)
    $register[0] = array_pop($stack);               // L6059 -> pop(32768)
    $register[0] = ($register[0] + 32767) % 32768;          // L6061 -> add(32768, 32768, 32767)
    a();                                                    // L6065 -> call(6027)
    return;                                                 // L6067 -> ret()
}