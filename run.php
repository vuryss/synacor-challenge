<?php /** @noinspection PhpUnhandledExceptionInspection */

$challenge = file_get_contents('challenge.bin');

$data = unpack('v*', $challenge);
$data = array_values($data);

$codes = [
    0  => ['halt', 0],
    1  => ['set' , 2],
    2  => ['push', 1],
    3  => ['pop' , 1],
    4  => ['eq'  , 3],
    5  => ['gt'  , 3],
    6  => ['jmp' , 1],
    7  => ['jt'  , 2],
    8  => ['jf'  , 2],
    9  => ['add' , 3],
    10 => ['mult', 3],
    11 => ['mod' , 3],
    12 => ['and' , 3],
    13 => ['or'  , 3],
    14 => ['not' , 2],
    15 => ['rmem', 2],
    16 => ['wmem', 2],
    17 => ['call', 1],
    18 => ['ret' , 0],
    19 => ['out' , 1],
    20 => ['in'  , 1],
    21 => ['noop', 0],
];

$position = 0;
$registers = [
    0 => 0,
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    6 => 0,
    7 => 0,
];
$stack = [];
$terminal = '';
$input = [
    'take tablet',
    'use tablet',
    'doorway',
    'north',
    'north',
    'bridge',
    'continue',
    'down',
    'east',
    'take empty lantern',
    'west',
    'west',
    'passage',
    'ladder',
    'west',
    'south',
    'north',
    'take can',
    'use can',
    'use lantern',
    'west',
    'ladder',
    'darkness',
    'continue',
    'west',
    'west',
    'west',
    'west',
    'north',
    'take red coin',
    'north',
    'east',
    'take concave coin',
    'down',
    'take corroded coin',
    'up',
    'west',
    'west',
    'take blue coin',
    'up',
    'take shiny coin',
    'down',
    'east',
    'use blue coin',
    'use red coin',
    'use shiny coin',
    'use concave coin',
    'use corroded coin',
    'north',
    'take teleporter',
    'use teleporter',
    'take strange book',
    'take business card',
    'fix teleporter',
    'use teleporter',
    'north',
    'north',
    'north',
    'north',
    'north',
    'north',
    'north',
    'north',
    'north',
    'take orb',
];
$input = implode("\n", $input) . "\n";

$action = false;

while (true) {
    $code = $codes[$data[$position]];
    $command = $code[0];
    $arguments = [];

    for ($i = 0; $i < $code[1]; $i++) {
        $arguments[] = $data[$position + $i + 1];
    }

    if ($position == 5489 || $action == 'insert values') {
        OpCodes::set('32768', '6'); // Register 0 to 6
        $action = false;
    }

    //if ($action == 'debug') {
        //echo 'L' . $position . ' -> ' . $command . '(' . implode(', ', $arguments) . ')' . PHP_EOL;
    //}

    $result = OpCodes::callCommand($command, $arguments);

    if ($action == 'fix_teleporter') {
        echo PHP_EOL . 'Fixing teleporter...' . PHP_EOL . PHP_EOL;
        OpCodes::wmem('5489', 21); // Remove slow function call
        OpCodes::wmem('5490', 21); // Remove slow function call
        OpCodes::set('32775', '25734'); // Register 7 to 25734
        $action = 'insert_values';
    }

    if ($result) {
        $position += $code[1] + 1;
    }
}

class OpCodes
{
    private static $skipAutoResolve = ['set', 'add', 'eq', 'pop', 'gt', 'and', 'or', 'not', 'mult', 'mod', 'rmem', 'in'];

    public static function callCommand($command, $arguments)
    {
        foreach ($arguments as $key => $argument) {
            if (!OpCodes::isValid($argument)) {
                throw new Exception('Invalid argument: ' . $argument);
            }

            if (!in_array($command, self::$skipAutoResolve)) {
                $arguments[$key] = self::getValue($argument);
            }
        }

        return self::{$command}(...$arguments);
    }

    private static function isValid($a)
    {
        return $a < 32776;
    }

    private static function getValue($a)
    {
        if (self::isRegister($a)) {
            global $registers;

            return $registers[self::getRegister($a)];
        }

        return $a;
    }

    private static function isRegister($a)
    {
        return $a > 32767;
    }

    private static function getRegister($a)
    {
        return $a - 32768;
    }

    public static function noop()
    {
        return true;
    }

    public static function out($a)
    {
        echo chr($a);

        return true;
    }

    public static function jmp($a)
    {
        global $position;
        $position = $a;

        return false;
    }

    public static function jt($a, $b)
    {
        if ($a) {
            global $position;
            $position = $b;

            return false;
        }

        return true;
    }

    public static function jf($a, $b)
    {
        return self::jt($a == 0 ? 1 : 0, $b);
    }

    public static function set($a, $b)
    {
        if (!self::isRegister($a)) {
            throw new Exception('Invalid register');
        }

        $reg = self::getRegister($a);

        global $registers;

        $registers[$reg] = self::getValue($b);

        return true;
    }

    public static function add($a, $b, $c)
    {
        $result = (self::getValue($b) + self::getValue($c)) % 32768;

        return self::set($a, $result);
    }

    public static function eq($a, $b, $c)
    {
        if (!self::isRegister($a)) {
            throw new Exception('Invalid register');
        }

        if (self::getValue($b) == self::getValue($c)) {
            self::set($a, 1);
        } else {
            self::set($a, 0);
        }

        return true;
    }

    public static function push($a)
    {
        global $stack;
        $stack[] = $a;

        return true;
    }

    public static function pop($a)
    {
        if (!self::isRegister($a)) {
            throw new Exception('Invalid register');
        }

        global $stack;

        if (empty($stack)) {
            throw new Exception('Empty stack');
        }

        return self::set($a, array_pop($stack));
    }

    public static function gt($a, $b, $c)
    {
        if (self::getValue($b) > self::getValue($c)) {
            return self::set($a, 1);
        }

        return self::set($a, 0);
    }

    public static function and($a, $b, $c)
    {
        return self::set($a, self::getValue($b) & self::getValue($c));
    }

    public static function or($a, $b, $c)
    {
        return self::set($a, self::getValue($b) | self::getValue($c));
    }

    public static function not($a, $b)
    {
        return self::set($a, 32767 - self::getValue($b));
    }

    public static function call($a)
    {
        global $stack, $position;
        $stack[] = $position + 2;
        $position = self::getValue($a);
        return false;
    }

    public static function mult($a, $b, $c)
    {
        $result = (self::getValue($b) * self::getValue($c)) % 32768;

        return self::set($a, $result);
    }

    public static function mod($a, $b, $c)
    {
        $result = self::getValue($b) % self::getValue($c);

        return self::set($a, $result);
    }

    public static function rmem($a, $b)
    {
        global $data;
        return self::set($a, $data[self::getValue($b)]);
    }

    public static function wmem($a, $b)
    {
        global $data;
        $data[self::getValue($a)] = self::getValue($b);
        return true;
    }

    public static function ret()
    {
        global $position, $stack;

        if (empty($stack)) {
            self::halt();
        }

        $position = array_pop($stack);

        return false;
    }

    public static function in($a)
    {
        global $input, $action;

        if (empty($input)) {
            $input = readline() . "\n";
            readline_add_history($input);
        }

        $char = $input[0];
        $input = substr($input, 1);

        if (strpos($input, 'fix teleporter') === 0) {
            $action = 'fix_teleporter';
            $input = substr($input, 14);
        }

        echo $char;
        self::set($a, ord($char));
        return true;
    }

    public static function halt()
    {
        echo 'Commands: ';
        print_r(readline_list_history());
        exit;
    }

    private static function dumpInstructions()
    {
        global $data, $codes;

        for ($i = 0; $i < count($data); $i++) {
            if (!isset($codes[$data[$i]])) {
                continue;
            }

            $a = $codes[$data[$i]];

            $arguments = [];

            for ($j = 0; $j < $a[1]; $j++) {
                $arguments[] = $data[$i + $j + 1];
            }


            echo 'L' . $i . ' -> ' . $a[0] . '(' . implode(', ', $arguments) . ')' . PHP_EOL;

            $i += $a[1];
        }
    }
}