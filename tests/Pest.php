<?php

use Attla\{
    Pincryp\Factory as Pincryp,
    Support\Envir
};

Envir::set('APP_KEY', Pincryp::generateKey());

dataset('var-types', $types = [
    'alfa'      => $value = 'Now I am become Death, the destroyer of worlds.',
    'alfanum'   => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'special'   => '`~!@#$%^&*()\\][+={}/|:;"\'<>,.?-_',
    'acents'    => 'àáâãäÀÁÂÃÄ çÇ èéêëÈÉÊË ìíîïÌÍÎÏ ñÑ òóôõöÒÓÔÕÖ ùúûüÙÚÛÜ ýÿÝ',
    'japanese'  => '今、私は世界の破壊者である死になりました。',
    'mandarin'  => '现在我变成了死神，世界的毁灭者。',
    'hindi'     => 'अब मैं मृत्यु बन गया हूँ, संसारों का नाश करने वाला।',
    'int'       => 42,
    'float'     => 4.2,
    'array (SEQ)'   => [$seq = [4,2]],
    'array (ASSOC)' => [$assoc = ['four' => 4,'two' => 2]],
    'stdClass'      => $stdClass = (object) $assoc,
    'bool (FALSE)'          => false,
    'bool (TRUE)'           => true,
    'int (FALSE)'           => 0,
    'int (TRUE)'            => 1,
    'array (empty)'         => [[]],
    'stdClass (empty)'      => new \stdClass(),
    'GMP class'             => new \GMP(0),
    'string numeric (FALSE)' => '0',
    'string numeric (TRUE)'  => '1',
    'null' => null,
    'null (byte)' => chr(0),
    'zero (byte)' => 0x0,
    'null string (byte)' => "\x00",
    'separator (byte)' => "\x1c",
    'byte' => 0x2A,
    'others' => " \t\n\r\0\x0B\x0c\xc2\xa0",
]);
