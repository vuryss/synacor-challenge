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

a();

// L5489 -> call(6027)

function a() {
    global $register, $stack;

    if ($register[0] != 0) {                                // L 6027
        if ($register[1] != 0) {                            // L 6035
            $stack[] = $register[0];                        // L 6048
            $register[1] = ($register[1] + 32767) % 32768;  // L 6050
            $stack[] = 6056;                                // L 6054
            a();                                            // L 6054
        } else {
            $register[0] = ($register[0] + 32767) % 32768;  // L6038 -> add(32768, 32768, 32767)
            $register[1] = $register[7];                    // L6042 -> set(32769, 32775)
            $stack[] = 6047;                                // L6045 -> call(6027)
            a();                                            // L6045 -> call(6027)
        }
    } else {
        $register[0] = ($register[1] + 1) % 32768;          // L 6030

                                                            // L 6034, 6047, 6067
        do {
            $line = array_pop($stack);
        } while (in_array($line, [6047, 6067, 6034]));

        if ($line != 6056) {
            throw new Exception('Something not right');
        }

        $register[1] = $register[0];                        // L6056 -> set(32769, 32768)
        $register[0] = array_pop($stack);           // L6059 -> pop(32768)
        $register[0] = ($register[0] + 32767) % 32768;      // L6061 -> add(32768, 32768, 32767)
        $stack[] = 6067;                                    // L6065 -> call(6027)
        a();                                                // L6065 -> call(6027)
    }
}

print_r($register);
print_r($stack);