<?php

namespace Attla\Pincryp;

use Attla\Support\Generic;
use Illuminate\Support\Str;
use Tuupola\Base58;

class Config extends \Attla\Support\AbstractData
{
    /**
     * Entropy length
     *
     * @var int
     */
    public int $entropy = 4;

    /**
     * Base64 characters
     *
     * @var string
     */
    public string $baseAlphabet = Base58::BITCOIN;

    /**
     * Base64 replacement characters
     *
     * @var string
     */
    public null|string $alphabet = null;

    /**
     * Alphabet seed
     *
     * @var int
     */
    public null|int $seed = null;

    /**
     * Encryption secret key
     *
     * @var string
     */
    public null|string $key = null;

    /**
     * Set secret key
     *
     * @param string $secret
     * @return string
     */
    public function setKey($secret)
    {
        if (empty($key = trim($secret ?: ''))) {
            return null;
        }

        if (Str::startsWith($key, $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return hash('sha256', $key, true);
    }

    /**
     * Set seed
     *
     * @param mixed $value
     * @param mixed $old
     * @return null|int
     */
    public function setSeed($value, $old): null|int
    {
        if (is_null($value)) {
            return null;
        }

        $value = is_int($value) ? abs($value) : Generic::toInt($value);

        if (is_null($old) || $old != $value) {
            $this->alphabet = Generic::sortBySeed($this->baseAlphabet, $value);
        }

        return $value;
    }

    /**
     * Set entropy
     *
     * @param mixed $entropy
     * @return int
     */
    public function setEntropy($value): int
    {
        if (is_null($value)) {
            return 0;
        }

        return is_int($value) ? abs($value) : Generic::toInt($value);
    }
}
