<?php

use Attla\{
    Pincryp\Config,
    Support\Str
};

it(
    'type is valid [Laravel] [unique]',
    fn ($value) => assertEquals(
        $value,
        Pincryp::decode(
            Pincryp::encode($value),
            is_array($value) ? true : false
        )
    )
)->with('var-types');

it(
    'always [Laravel] [unique]',
    fn ($value) => assertEquals(6, count(array_unique(array_map(
        fn() => Pincryp::encode($value),
        range(0, 5)
    ))))
)->with('var-types');


$wrongConfig = new Config([
    'key' => $key = Str::randHex(),
]);
it(
    'decode is invalid [Laravel] [unique]',
    fn($value) => assertTrue(
        ($encoded = Pincryp::encode($value)) && Pincryp::setConfig($wrongConfig)->decode(
            $encoded,
            is_array($value) ? true : false
        ) !== $value
    )
)->with('var-types');
