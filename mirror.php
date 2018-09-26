<?php

$code = 'lMvqTHllHbqV';
$code = strrev($code);
$code = str_replace(['q', 'b'], ['p', 'd'], $code);

echo $code;