<?php

use Attla\Support\Envir;

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
    'key' => Envir::get('app.key', Envir::get('APP_KEY')),
];
