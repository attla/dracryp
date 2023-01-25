<?php

use Attla\Pincryp\Factory as Pincryp;

it(
    'type is valid [unique]',
    fn ($value) => assertEquals(
        Pincryp::decode(
            Pincryp::encode($value),
            '',
            is_array($value) ? true : false
        ),
        $value
    )
)->with('var-types');

it(
    'always [unique]',
    fn ($value) => assertEquals(count(array_unique(array_map(
        fn() => Pincryp::encode($value),
        range(0, 5)
    ))), 6)
)->with('var-types');

it(
    'decode is invalid [unique]',
    fn ($value) => assertTrue(
        Pincryp::decode(
            Pincryp::encode($value),
            42,
            is_array($value) ? true : false
        ) !== $value
    )
)->with('var-types');

it(
    'type is valid [same]',
    fn ($value) => !Pincryp::setEntropy(0) && assertEquals(
        Pincryp::decode(
            Pincryp::encode($value),
            '',
            is_array($value) ? true : false
        ),
        $value
    )
)->with('var-types');

it(
    'always [same]',
    fn ($value) => assertEquals(count(array_unique(array_map(
        fn() => Pincryp::encode($value),
        range(0, 5)
    ))), 1)
)->with('var-types');

it(
    'decode is invalid [same]',
    fn ($value) => assertTrue(
        Pincryp::decode(
            Pincryp::encode($value),
            42,
            is_array($value) ? true : false
        ) !== $value
    )
)->with('var-types');
