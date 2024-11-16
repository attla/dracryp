<?php

namespace Benchmark;

use Attla\{
    Pincryp\Config,
    Pincryp\Factory as Pincryp,
    Support\Str
};

class PincrypBench
{
    /** @Revs(2000) */
    public function benchConsume()
    {
        $pincryp = new Pincryp(new Config([
            'key' => Str::randHex(),
        ]));

        $pincryp->encode([
            'sub' => "1234567890",
            'name' => 'John Doe',
            'iat' => 1516239022
        ]);
    }
}
