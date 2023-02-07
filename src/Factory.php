<?php

namespace Attla\Pincryp;

use Attla\Support\{
    Arr as AttlaArr,
    Str as AttlaStr,
    UrlSafe
};
use Illuminate\Support\Str;

class Factory
{
    /**
     * List of acceptable variable types
     *
     * @var array<string, int>
     */
    public const ACCEPTED_TYPES = [
        'NULL'      => self::NULL,
        'boolean'   => self::BOOL,
        'integer'   => self::INT,
        'double'    => self::FLOAT,
        'string'    => self::STRING,
        'array'     => self::ARRAY,
        'object'    => self::OBJECT,
    ];

    /**
     * Identification of var types
     *
     * @var int
     */
    public const NULL   = 0;
    public const BOOL   = 1;
    public const STRING = 2;
    public const INT    = 3;
    public const FLOAT  = 4;
    public const ARRAY  = 5;
    public const OBJECT = 6;

    /**
     * Types aliases
     *
     * @var int
     */
    public const BOOLEAN    = self::BOOL;
    public const STR        = self::STRING;
    public const INTEGER    = self::INT;
    public const DOUBLE     = self::FLOAT;
    public const OBJ        = self::STRING;

    /**
     * Type indication separator
     *
     * @var string
     */
    private string $separator = "\x1c";

    /**
     * String encoding
     *
     * @var string
     */
    private string $encoding = '8bit';

    /**
     * Config instance
     *
     * @var \Attla\Pincryp\Config
     */
    public Config $config;

    /**
     * Last config instance
     *
     * @var \Attla\Pincryp\Config
     */
    public null|Config $lastConfig = null;

    /**
     * Create a new factory instance
     *
     * @param \Attla\Pincryp\Config $config
     * @return void
     */
    public function __construct(Config $config = null)
    {
        $this->setConfig($config ?? new Config());
    }

    /**
     * Set config instance
     *
     * @param \Attla\Pincryp\Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = clone $config;
        return $this;
    }

    /**
     * Set config instance
     *
     * @param \Attla\Pincryp\Config $config
     * @return $this
     */
    public function onceConfig(Config $config)
    {
        $this->lastConfig = $this->config;
        $this->config = clone $config;
        return $this;
    }

    /**
     * Set config instance
     *
     * @param string $secret
     * @return $this
     */
    public function onceKey(string $secret)
    {
        $this->lastConfig = clone $this->config;
        $this->config->key = $secret;
        return $this;
    }

    /**
     * Set config instance
     *
     * @param string $secret
     * @return $this
     */
    private function maybeRestoreConfig()
    {
        if (!is_null($this->lastConfig)) {
            $this->config = clone $this->lastConfig;
            $this->lastConfig = null;
        }

        return $this;
    }

    /**
     * Validate config instance
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateConfig()
    {
        if (!is_string($key = $this->config->key) || empty($key = trim($key))) {
            throw new \InvalidArgumentException('Secret key is required for use attla/pincryp.');
        }
    }

    /**
     * Encrypt anything
     *
     * @param mixed $data
     * @return string
     */
    public function encode($data): string
    {
        $this->validateConfig();

        $entropy = $this->config->getInt('entropy');
        $entropy = $entropy ? random_bytes($entropy) : '';

        $encoded = static::maybeUseAlphabet(
            UrlSafe::base64Encode($this->cipher(
                $this->toText($data),
                $this->forgeKey($this->config->key, $entropy)
            ) . $entropy),
            $this->config->baseAlphabet,
            $this->config->alphabet
        );

        $this->maybeRestoreConfig();
        return $encoded;
    }

    /**
     * Decrypt an value
     *
     * @param mixed $data
     * @param bool $associative
     * @return mixed
     */
    public function decode($data, bool $associative = false)
    {
        $this->validateConfig();

        $binary = UrlSafe::base64Decode($this->maybeUseAlphabet(
            $data,
            $this->config->alphabet,
            $this->config->baseAlphabet
        ));
        $eLength = $this->config->getInt('entropy');
        $entropy = mb_substr($binary, -$eLength, $eLength, $this->encoding);

        $decoded = $this->convert($this->cipher(
            mb_substr($binary, 0, mb_strlen($binary, $this->encoding) - $eLength, $this->encoding),
            $this->forgeKey($this->config->key, $entropy)
        ), $associative);

        $this->maybeRestoreConfig();
        return $decoded;
    }

    /**
     * Convert value to original type
     *
     * @param string $value
     * @param bool $associative
     * @return mixed
     */
    private function convert(string $value, bool $associative = false)
    {
        if ($type = array_search(Str::before($value, $this->separator), static::ACCEPTED_TYPES)) {
            $value = Str::after($value, $this->separator);
            settype($value, $type);
            return $value;
        }

        if (Str::isJson($value)) {
            return json_decode($value, $associative);
        } elseif (AttlaStr::isSerialized($value)) {
            $value = unserialize($value);
            return $associative && is_array($value) ? $value : (object) $value;
        }

        return $value;
    }

    /**
     * Convert a value to string
     *
     * @param mixed $value
     * @return string
     */
    private function toText($value): string
    {
        if (!isset(static::ACCEPTED_TYPES[$type = gettype($value)])) {
            return '';
        } elseif (!in_array($type, ['array', 'object'])) {
            return static::ACCEPTED_TYPES[$type] . $this->separator . $value;
        }

        if (AttlaArr::canBeArray($value)) {
            return json_encode(empty($array = AttlaArr::toArray($value)) ? $value : $array);
        }

        return serialize($value);
    }

    /**
     * Cipher a string
     *
     * @param string $str
     * @param string $secret
     * @return string
     */
    private function cipher($str, string $key = '')
    {
        if (!$str || !$key || !mb_strlen($str = (string) $str, $this->encoding)) {
            return '';
        }

        $result = '';

        $dataLength = mb_strlen($str, $this->encoding) - 1;
        $keyLenght = mb_strlen($key, $this->encoding) - 1;

        do {
            $result .= $str[$dataLength] ^ $key[$dataLength % $keyLenght];
        } while ($dataLength--);

        return strrev($result);
    }

    /**
     * Apply alphabet if necessary
     *
     * @param mixed $data
     * @param null|string $from
     * @param null|string $to
     * @return mixed
     */
    private function maybeUseAlphabet($data, null|string $from, null|string $to)
    {
        if (
            !$data
            || !is_string($data)
            || is_null($from) || is_null($to)
            || $from == $to
        ) {
            return $data;
        }

        return strtr($data, $from, $to);
    }

    /**
     * Forge secret key with entropy
     *
     * @param string $secret
     * @param string $entropy
     * @return string
     */
    private function forgeKey(string $secret, string $entropy = '')
    {
        return empty($entropy = trim($entropy)) ? $secret : hash('sha256', $secret . $entropy, true);
    }
}
