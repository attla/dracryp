<?php

namespace Attla\Pincryp;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @method static string cipher($data, string $secret = '')
 * @method static string encode($data)
 * @method static mixed decode($data, bool $associative = false)
 * @method static $this setConfig(\Attla\Pincryp\Config $config)
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
        return 'pincryp';
    }
}
