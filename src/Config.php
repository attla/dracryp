<?php

namespace Attla\Pincryp;

use Attla\Support\{
    Envir,
    Generic
};
use Illuminate\Support\Str;

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
    public string $baseAlphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

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
    public string $key = '';

    /**
     * Set secret key
     *
     * @param string $secret
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function setKey(string $secret)
    {
        if (
            empty($key = trim($secret ?: ''))
            && is_string($key = Envir::get('APP_KEY'))
            && Str::startsWith($key, $prefix = 'base64:')
        ) {
            $key = Str::after($key, $prefix);
        }

        if (!is_string($key) || empty($key = trim($key))) {
            throw new \InvalidArgumentException('Secret key is required for use attla/pincryp.');
        }

        return hash('sha256', $secret, true);
    }

    /**
     * Set seed
     *
     * @param int|string $seed
     * @param mixed $old
     * @return void
     */
    public function setSeed($value, $old)
    {
        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = Generic::toInt($value);
        }

        if (is_null($value) || $old != $value) {
            $this->alphabet = Generic::sortBySeed($this->baseAlphabet, $value);
        }

        return $value;
    }
}