# Pincryp

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-lightgrey.svg" alt="License"></a>
<a href="https://packagist.org/packages/attla/pincryp"><img src="https://img.shields.io/packagist/v/attla/pincryp" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/attla/pincryp"><img src="https://img.shields.io/packagist/dt/attla/pincryp" alt="Total Downloads"></a>
</p>

🧛 A layer of encryption a little too judicious.

## Installation

```bash
composer require attla/pincryp
```

## Usage

```php

use Attla\Pincryp\Config;
use Attla\Pincryp\Factory as Pincryp;

// create config instance
$config = new Config();
$config->key = 'hic sunt dracones';
// or
$config = new Config(['key' => 'hic sunt dracones']);

// creating Pincryp instance
$pincryp = new Pincryp($config);

// encoding
$encoded = $pincryp->encode('this is something to encode..');
echo 'encoded: ' . $encoded.PHP_EOL;

$decoded = $pincryp->decode($encoded);
echo 'decoded: ' . $decoded.PHP_EOL;

```

The Pincryp can encrypt all primitive types: `array`, `stdClass`, `bool`, `string`, `integer`, `float` and `null`.

See an example of array encryption:

```php

// encoding
$encoded = $pincryp->encode([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
echo 'encoded: ' . $encoded.PHP_EOL;

// to return a stdClass pass the second param as TRUE
$decoded = $pincryp->decode($encoded, false);
echo 'decoded: ' . $decoded.PHP_EOL;

```

### Config params

| Parameter | Type | Description |
|--|--|--|
| ``key`` | String | Encryption secret key |
| ``entropy`` | Integer | Entropy length to generate unique results, set zero to every same result |
| ``seed`` | String, Integer, Null | Alphabet base seed to create a unique dictionary |

## License

This package is licensed under the [MIT license](LICENSE) © [Zunq](https://zunq.com).
