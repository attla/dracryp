<?php

namespace Attla\Pincryp;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @method static string cipher($data, string $secret = '')
 * @method static string encode($data, string $secret = '')
 * @method static mixed decode($data, string $secret = '', bool $associative = false)
 *
 * @method static void setKey(string $secret)
 * @method static void setSeed(int|string $seed)
 * @method static void setEntropy(int $length = 4)
 * @method static string getKey(string $secret = '', string $entropy = '')
 * @method static string generateKey(int $length = 32)
 * @method static string toText($value)
 * @method static string md5($str, string $secret = '')
 * @method static string sha1($str, string $secret = '')
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
