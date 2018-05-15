<?php
function add($a, $b) {
    return $a + $b;
}

function sum($values) {
    $sum = 0;
    foreach ($values as $value) {
        $sum = add($sum, $value);
    }
    return $sum;
}

$five = add(2, 3);
$ten = add(add(2, 3), 5);
$hundred = sum(array(10, 20, 30, 40));
$thousand = sum(array(100, 200, 300, add(399, 1)));