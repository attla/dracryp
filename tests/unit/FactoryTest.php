<?php

use Attla\Pincryp\Factory as Pincryp;

it(
    'type is valid',
    fn ($value) => $this->assertEquals(
        Pincryp::decode(
            Pincryp::encode($value),
            '',
            is_array($value) ? true : false
        ),
        $value
    )
)->with('var-types');

it(
    'always different',
    fn ($value) => $this->assertEquals(count(array_unique(array_map(
        fn() => Pincryp::encode($value),
        range(0, 5)
    ))), 6)
)->with('var-types');

it(
    'always same',
    fn ($value) => !Pincryp::setEntropy(0) && $this->assertEquals(count(array_unique(array_map(
        fn() => Pincryp::encode($value),
        range(0, 5)
    ))), 1)
)->with('var-types');

it(
    'decode is invalid',
    fn ($value) => $this->assertTrue(
        Pincryp::decode(
            Pincryp::encode($value),
            42,
            is_array($value) ? true : false
        ) !== $value
    )
)->with('var-types');
