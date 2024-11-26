<?php

namespace Benchmark;

use Attla\{
    Pincryp\Config,
    Pincryp\Factory as Pincryp,
};

class PincrypBench
{
    /**
     * Secret passphrase
     *
     * @var string
     */
    protected $secret = 'Now I am become Death, the destroyer of worlds.';

    /**
     * The pincryp instance
     *
     * @var \Attla\Pincryp\Factory
     */
    protected $pincryp;

    /**
     * Benchmark data example
     *
     * @var array
     */
    protected $data = [
        'sub' => "1234567890",
        'name' => 'John Doe',
        'iat' => 1516239022
    ];

    /**
     * Benchmark encoded data example
     *
     * @var string
     */
    protected $encodedData = '';

    public function __construct()
    {
        $this->pincryp = new Pincryp(new Config([
            'key' => $this->secret,
        ]));

        $this->encodedData = $this->pincryp->encode($this->data);
    }

    /** @Revs(2000) */
    public function benchEncode()
    {
        $this->pincryp->encode($this->data);
    }

    /** @Revs(2000) */
    public function benchDecode()
    {
        $this->pincryp->decode($this->encodedData);
    }

    /** @Revs(2000) */
    public function benchEncode_Decode()
    {
        $this->pincryp->decode($this->pincryp->encode($this->data));
    }
}
