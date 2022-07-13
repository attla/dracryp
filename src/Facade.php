<?php

namespace Attla\Pincryp;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @method static string hash(string $password, string $salt = '')
 * @method static string generateKey(int $length = 32)
 * @method static bool hashEquals(string $unencrypted, string $encrypted)
 * @method static string toText($item)
 * @method static string getSecret()
 * @method static string encode($data, string $secret = '')
 * @method static mixed decode($data, string $secret = '', bool $assoc = false)
 * @method static string urlsafeB64Encode(string $data)
 * @method static string urlsafeB64Decode(string $data)
 * @method static string md5($str, string $secret = '')
 * @method static bool isSerialized($data)
 * @method static bool isHttpQuery($data)
 *
 * @see \Attla\Pincryp\Factory
 */
class Facade extends BaseFacade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
