<?php

return [
    /**
     * Entropy length to generate unique results
     *
     * @var int
     */
    'entropy' => 4,

    /**
     * Alphabet base seed to create a unique dictionary
     *
     * @var int|string
     */
    'seed' => null,

    /**
     * Encryption secret key
     *
     * @var string
     */
    'key' => env('APP_KEY'),
];
