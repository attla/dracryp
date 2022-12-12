<?php

namespace Attla\Pincryp;

use Attla\Support\{
    Arr as AttlaArr,
    Str as AttlaStr,
    Envir,
    Generic,
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
    private static string $separator = "\x1c";

    /**
     * String encoding
     *
     * @var string
     */
    private static string $encoding = '8bit';

    /**
     * Entropy length
     *
     * @var int
     */
    private static int $entropy = 4;

    /**
     * Base64 characters
     *
     * @var string
     */
    public static string $baseAlphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

    /**
     * Base64 replacement characters
     *
     * @var string
     */
    public static null|string $alphabet = null;

    /**
     * Alphabet seed
     *
     * @var int
     */
    private static null|int $seed = null;

    /**
     * Encryption secret key
     *
     * @var string
     */
    private static string $key = '';

    /**
     * Create a new encryption key
     *
     * @param int $length Optionally, a length of bytes to use
     * @return string
     */
    public static function generateKey(int $length = 32): string
    {
        do {
            $key = bin2hex(random_bytes($length));
        } while (!$key);

        return $key;
    }

    /**
     * Encrypt anyting
     *
     * @param mixed $data
     * @param string $secret
     * @return string
     */
    public static function encode($data, string $secret = ''): string
    {
        return static::maybeUseAlphabet(
            UrlSafe::base64Encode(static::cipher(
                static::toText($data),
                static::getKey($secret, $entropy = static::$entropy ? static::generateKey(static::$entropy) : '')
            ) . hex2bin($entropy)),
            static::$baseAlphabet,
            static::$alphabet
        );
    }

    /**
     * Decrypt an value
     *
     * @param mixed $data
     * @param string $secret
     * @param bool $associative
     * @return mixed
     */
    public static function decode($data, string $secret = '', bool $associative = false)
    {
        $binary = UrlSafe::base64Decode(static::maybeUseAlphabet(
            $data,
            static::$alphabet,
            static::$baseAlphabet
        ));
        $entropy = bin2hex(mb_substr($binary, -static::$entropy, static::$entropy, static::$encoding));

        return static::convert(static::cipher(
            mb_substr($binary, 0, mb_strlen($binary, static::$encoding) - static::$entropy, static::$encoding),
            static::getKey($secret, $entropy)
        ), $associative);
    }

    /**
     * Convert value to original type
     *
     * @param string $value
     * @param bool $associative
     * @return mixed
     */
    private static function convert(string $value, bool $associative = false)
    {
        if ($type = array_search(Str::before($value, static::$separator), static::ACCEPTED_TYPES)) {
            $value = Str::after($value, static::$separator);
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
    public static function toText($value): string
    {
        if (!isset(static::ACCEPTED_TYPES[$type = gettype($value)])) {
            return '';
        } elseif (!in_array($type, ['array', 'object'])) {
            return static::ACCEPTED_TYPES[$type] . static::$separator . $value;
        }

        if (AttlaArr::canBeArray($value)) {
            return json_encode(empty($array = AttlaArr::toArray($value)) ? $value : $array);
        }

        return serialize($value);
    }

    /**
     * Set secret key
     *
     * @param string $secret
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function setKey(string $secret)
    {
        static::$key = static::getValidKey($secret);
    }

    /**
     * Get secret key
     *
     * @param string $secret
     * @param string $entropy
     * @return string
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public static function getKey(string $secret = '', string $entropy = ''): string
    {
        if (!empty($secret = trim($secret ?: static::$key))) {
            return empty($entropy = trim($entropy)) ? $secret : static::getValidKey($secret . $entropy);
        }

        if (is_string($key = Envir::get('APP_KEY')) && Str::startsWith($key, $prefix = 'base64:')) {
            $key = Str::after($key, $prefix);
        }

        if (!is_string($key) && empty($key)) {
            throw new \Exception('APP_KEY is required for use attla/pincryp.');
        }

        return static::getKey(static::$key = static::getValidKey($key), $entropy);
    }

    /**
     * Get valid secret key
     *
     * @param string $secret
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected static function getValidKey(string $secret): string
    {
        if (empty($secret = trim($secret))) {
            throw new \InvalidArgumentException('Invalid secret key.');
        }

        return hash('sha256', $secret, true);
    }

    /**
     * Set seed
     *
     * @param int|string $seed
     * @return void
     */
    public static function setSeed(int|string $seed)
    {
        if (is_string($seed)) {
            $seed = Generic::toInt($seed);
        }

        if (is_null(static::$seed) || static::$seed != $seed) {
            static::$alphabet = Generic::sortBySeed(static::$baseAlphabet, static::$seed = $seed);
        }
    }

    /**
     * Set entropy length
     *
     * @param int $length
     * @return void
     */
    public static function setEntropy(int $length = 4)
    {
        static::$entropy = $length;
    }

    /**
     * Cipher a string
     *
     * @param string $str
     * @param string $secret
     * @return string
     */
    public static function cipher($str, string $secret = '')
    {
        if (!mb_strlen($str, static::$encoding) or !$secret = static::getKey($secret)) {
            return '';
        } elseif (!is_string($str)) {
            $str = (string) $str;
        }

        $result = '';

        $dataLength = mb_strlen($str, static::$encoding) - 1;
        $secretLenght = mb_strlen($secret, static::$encoding) - 1;

        do {
            $result .= $str[$dataLength] ^ $secret[$dataLength % $secretLenght];
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
    private static function maybeUseAlphabet($data, null|string $from, null|string $to)
    {
        if (
            !$data
            || !is_string($data)
            || is_null(static::$alphabet)
            || static::$alphabet == static::$baseAlphabet
        ) {
            return $data;
        }

        return strtr($data, $from, $to);
    }

    /**
     * Encrypt md5 bytes of a string
     *
     * @param mixed $data
     * @param string $secret
     * @return string
     */
    public static function md5($data, string $secret = ''): string
    {
        return static::encode(md5((string) $data, true), $secret);
    }

    /**
     * Encrypt sha1 bytes of a string
     *
     * @param mixed $data
     * @param string $secret
     * @return string
     */
    public static function sha1($data, string $secret = ''): string
    {
        return static::encode(sha1((string) $data, true), $secret);
    }
}
