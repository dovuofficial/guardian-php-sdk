# A PHP SDK to work with The Guardian

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonwood2/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/jonwood2/guardian-php-sdk)
[![Tests](https://github.com/jonwood2/guardian-php-sdk/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/jonwood2/guardian-php-sdk/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/jonwood2/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/jonwood2/guardian-php-sdk)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require dovuofficial/guardian-php-sdk
```

## Usage

```php
$sdk = new Dovu\GuardianPhpSdk();
$response = $sdk->accounts->create('username','password');
$response =  $sdk->accounts->login('username','password');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [jonwood2](https://github.com/jonwood2)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
