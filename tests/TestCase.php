<?php

namespace Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Attla\Pincryp\PincrypServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Pincryp' => \Attla\Pincryp\Facade::class,
        ];
    }
}
