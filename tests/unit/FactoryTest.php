<?php

use Attla\{
    Pincryp\Config,
    Support\Str
};

$config = new Config();
$config->key = Str::randHex();

$configWrong = clone $config;
$configWrong->key = Str::randHex();

// same config
$configSame = clone $config;
$configSame->entropy = 0;

$configSameWrong = clone $configWrong;
$configSameWrong->entropy = 0;

// seeded config
$configSeeded = clone $configSame;
$configSeeded->seed = 42;

$configSeededWrong = clone $configSameWrong;
$configSeededWrong->seed = 42;


// setConfig
it(
    'setConfig() valid',
    function ($value) use ($config, $configWrong) {
        $pincryp = clone pincryp($config);

        assertNotSame(
            $value,
            $pincryp->setConfig($configWrong)
                ->decode(encode($config, $value))
        );
    }
)->with('string');

// once config
it(
    'once config valid',
    function ($value) use ($configSame) {
        $pincryp = pincryp($configSame);
        $config = clone $pincryp->config;
        $config->key = $value;

        $onceEncoded = $pincryp->onceConfig($config)->encode($value);

        assertEquals(
            $value,
            $pincryp->onceConfig($config)->decode($onceEncoded)
        );

        assertEquals(
            $value,
            $pincryp->decode(
                $encoded = $pincryp->encode($value)
            )
        );

        assertNotSame($encoded, $onceEncoded);
    }
)->with('string');

// once key
it(
    'once key valid',
    function ($value) use ($configSame) {
        $pincryp = pincryp($configSame);
        $onceEncoded = $pincryp->onceKey($value)->encode($value);

        assertEquals(
            $value,
            $pincryp->onceKey($value)->decode($onceEncoded)
        );

        assertEquals(
            $value,
            $pincryp->decode(
                $encoded = $pincryp->encode($value)
            )
        );

        assertNotSame($encoded, $onceEncoded);
    }
)->with('string');

// unique
it(
    'type is valid [unique]',
    fn ($value) => assertEquals(
        $value,
        encodeAndDecode(
            $config,
            $value,
            is_array($value) ? true : false
        )
    )
)->with('var-types');

it(
    'always [unique]',
    fn ($value) => assertEquals(6, count(array_unique(array_map(
        fn() => encode($config, $value),
        range(0, 5)
    ))))
)->with('value');

it(
    'decode with wrong key is invalid [unique]',
    fn ($value) => assertNotSame(
        $value,
        decode(
            $configWrong,
            encode($config, $value),
            is_array($value) ? true : false
        )
    )
)->with('var-types');

it(
    'always [same]',
    fn ($value) => assertEquals(1, count(array_unique(array_map(
        fn() => encode($configSame, $value),
        range(0, 5)
    ))))
)->with('value');

it(
    'decode with wrong key is invalid [same]',
    fn ($value) => assertNotSame(
        $value,
        decode(
            $configSameWrong,
            encode($configSame, $value),
            is_array($value) ? true : false
        )
    )
)->with('var-types');

// seeded same
it(
    'type is valid [seeded] [same]',
    fn ($value) => assertEquals(
        $value,
        encodeAndDecode(
            $configSeeded,
            $value,
            is_array($value) ? true : false
        )
    )
)->with('var-types');

it(
    'always [seeded] [same]',
    fn ($value) => assertEquals(1, count(array_unique(array_map(
        fn() => encode($configSeeded, $value),
        range(0, 5)
    ))))
)->with('value');

it(
    'decode with wrong key is invalid [seeded] [same]',
    fn ($value) => assertNotSame(
        $value,
        decode(
            $configSeededWrong,
            encode($configSeeded, $value),
            is_array($value) ? true : false
        )
    )
)->with('var-types');

it(
    'seeded is not the same as not seeded [seeded] [same]',
    fn ($value) => assertNotSame(
        encode($configSame, $value),
        encode($configSeeded, $value),
    )
)->with('value');
