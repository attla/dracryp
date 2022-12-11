<?php

namespace Attla\Benchmark;

use Attla\Pincryp\Factory as Pincryp;

class PincrypBench
{
    /**
     * @Revs(10000)
     */
    public function benchConsume()
    {
        Pincryp::encode([
            'sub' => "1234567890",
            'name' => 'John Doe',
            'iat' => 1516239022
        ], Pincryp::generateKey());
    }
}
