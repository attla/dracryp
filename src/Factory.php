<?php

namespace Attla\Pincryp;

use Illuminate\Support\Str;
use Illuminate\Support\Enumerable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Factory
{
    /**
     * Encryption secret key
     *
     * @var string
     */
    private static $key;

    /**
     * Method to convert to string
     *
     * @var string
     */
    private static $toString;

    /**
     * Encrypt a string following a salt, salt should not be passed if it is not a hash
     *
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function hash(string $password, string $salt = ''): string
    {
        if ($salt) {
            $saltLength = strlen($salt);
            if ($saltLength > 40) {
                $saltLength -= 40;
                $salt = $saltLength % 2 ? substr($salt, 0, $saltLength) : substr($salt, -$saltLength);
            }
        } else {
            $length = 47 % strlen($password);
            if ($length == 0) {
                $length = 47 % mt_rand(2, 46);
            }

            do {
                $salt = substr(static::generateKey(24), 0, $length);
            } while (!$salt);

            $saltLength = strlen($salt);
        }

        $restDiv = $saltLength % 2;
        return ($restDiv ? $salt : '') . sha1($password . $salt) . ($restDiv ? '' : $salt);
    }

    /**
     * Create a new encryption key
     *
     * @param int $length Optionally, a length of bytes to use
     * @return string
     */
    public static function generateKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Compare an unencrypted password with an encrypted password
     *
     * @param string $unencrypted
     * @param string $encrypted
     * @return bool
     */
    public static function hashEquals(string $unencrypted, string $encrypted)
    {
        return hash_equals($encrypted, static::hash($unencrypted, $encrypted));
    }

    /**
     * Convert a value to string
     *
     * @param array|\stdClass $value
     * @return string
     */
    public static function toText($value): string
    {
        if ($value instanceof Enumerable) {
            $value = $value->all();
        } elseif ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof Jsonable) {
            $value = json_decode($value->toJson(), true);
        } elseif ($attributes instanceof \JsonSerializable) {
            $value = (array) $value->jsonSerialize();
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        } elseif (!is_array($value)) {
            $value = (array) $value;
        }

        if (!in_array($mode = static::getToStringMode(), ['query', 'json', 'serialize'])) {
            $mode = 'query';
        }

        return $mode == 'query' ? http_build_query($value)
            : ($mode == 'json'
                ? json_encode($value)
                : serialize($value)
            );
    }

    /**
     * Get secret key
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getKey(): string
    {
        if (static::$key) {
            return static::$key;
        }

        if (!$key = env('APP_KEY')) {
            throw new \Exception('APP_KEY is required for use attla/pincryp.');
        }

        return static::$key = $key;
    }

    /**
     * Get to string mode
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getToStringMode(): string
    {
        if (static::$toString) {
            return static::$toString;
        }

        return static::$toString = env('APP_TO_STRING', 'query');
    }

    /**
     * Cipher a string
     *
     * @param string $str
     * @param string $secret
     * @return string
     */
    protected static function cipher($str, $secret)
    {
        $secret = $secret ?: static::getKey();

        if (!$str || !$secret) {
            return '';
        }

        if (!is_string($str)) {
            $str = (string) $str;
        }

        $result = '';

        $dataLength = strlen($str) - 1;
        $secretLenght = strlen($secret) - 1;

        do {
            $result .= $str[$dataLength] ^ $secret[$dataLength % $secretLenght];
        } while ($dataLength--);

        return strrev($result);
    }

    /**
     * Encrypt a anyting with secret key
     *
     * @param mixed $data
     * @param string $secret
     * @return string
     */
    public static function encode($data, string $secret = ''): string
    {
        if (is_array($data) || is_object($data)) {
            $data = static::toText($data);
        }

        return static::urlsafeB64Encode(static::cipher($data, $secret));
    }

    /**
     * Decrypt a anyting with secret key
     *
     * @param mixed $data
     * @param string $secret
     * @param bool $assoc
     * @return mixed
     */
    public static function decode($data, string $secret = '', bool $assoc = false)
    {
        if ($result = static::cipher(static::urlsafeB64Decode($data), $secret)) {
            if (Str::isJson($result)) {
                $result = json_decode($result, $assoc);
            } elseif (static::isSerialized($result)) {
                $result = unserialize($result);
                if (!$assoc) {
                    $result = (object) $result;
                }
            } elseif (static::isHttpQuery($result)) {
                parse_str($result, $array);
                $result = !$assoc ? (object) $array : $array;
            }
        }

        return $result;
    }

    /**
     * Encode a string with URL-safe Base64
     *
     * @param string $input The string you want encoded
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode(string $data): string
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-.'));
    }

    /**
     * Decode a string with URL-safe Base64
     *
     * @param string $data A Base64 encoded string
     * @return string A decoded string
     */
    public static function urlsafeB64Decode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($data, '-.', '+/'));
    }

    /**
     * Encrypt an md5 in bytes of a string
     *
     * @param mixed $str
     * @param string $secret
     * @return string
     */
    public static function md5($str, string $secret = ''): string
    {
        return static::encode(md5((string) $str, true), $secret);
    }

    /**
     * Check value to find if it was serialized
     *
     * @param string $data
     * @return bool
     */
    public static function isSerialized($data)
    {
        if (!is_string($data) || !$data) {
            return false;
        }

        try {
            $unserialized = @unserialize($data);
        } catch (\Exception) {
            return false;
        }

        return serialize($unserialized) === $data;
    }

    /**
     * Check if it is a valid http query
     *
     * @param string $data
     * @return bool
     */
    public static function isHttpQuery($data)
    {
        if (!is_string($data) || !$data) {
            return false;
        }

        return preg_match('/^([+\w\.\/%_-]+=([+\w\.\/%_-]*)?(&[+\w\.\/%_-]+(=[+\w\.\/%_-]*)?)*)?$/', $data)
            ? true
            : false;
    }
}
