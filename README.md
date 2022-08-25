# A PHP SDK to work with The Guardian

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jonwood2/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/jonwood2/guardian-php-sdk)
[![Tests](https://github.com/jonwood2/guardian-php-sdk/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/jonwood2/guardian-php-sdk/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/jonwood2/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/jonwood2/guardian-php-sdk)

This SDK lets you perform API calls to Dovu's Guardian API middleware. Making working with Hedera Guardian more streamlined.

## Installation

You can install the package via composer:

```bash
composer require dovuofficial/guardian-php-sdk
```

## Usage

```php
$sdk = new Dovu\GuardianPhpSdk();

$sdk->setHmacSecret('hmac_secret');

$response = $sdk->accounts->create('username','password');
$response = $sdk->accounts->login('username','password');
```

## Example Flow

Assumes a Standard Registry account already exists which has published a policy.

- Create A New Registrant Account
- Create A New Verifier Account
- Assign The New Accounts To A Policy
- Login With The Registrant Account
- Submit An Application
- Login With The Standard Registry Account
- Approve The Application
- Login With The Registrant Account
- Submit An Ecological Project
- Login With The Standard Registry Account
- Approve The Ecological Project
- Login With The Registrant Account
- Submit A New MRV Request
- Login With The Verifier Account
- Approve The MRV Request

After the MRV request is approved, tokens will automatically be minted that represent the carbon described in the MRV for this Ecological Project.

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
