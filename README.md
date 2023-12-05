# A PHP SDK to work with The Guardian

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dovuofficial/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/dovuofficial/guardian-php-sdk)
[![Tests](https://github.com/dovuofficial/guardian-php-sdk/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/dovuofficial/guardian-php-sdk/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/dovuofficial/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/dovuofficial/guardian-php-sdk)

This SDK lets you perform API calls to Dovu's Guardian API middleware. Making working with Hedera Guardian more streamlined.

## Installation

You can install the package via composer:

```bash
composer require dovuofficial/guardian-php-sdk
```

## Usage with Raw SDK

```php
$sdk = new Dovu\GuardianPhpSdk();

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('hmac_secret');

$sdk->addNotification(['slack' => 'https://hooks.slack.com/services/xxxxxxx']);

$response = $sdk->accounts->create('username','password');
$response = $sdk->accounts->login('username','password');
```

## Usage with Guardian SDK Helper 

```php

$sdk = new Dovu\GuardianPhpSdk();

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$this->helper = new GuardianSDKHelper($sdk);

$registrant = $this->helper->createNewUser('username', 'secret');

$supplier_token = $registrant['data']['accessToken'];

$this->helper->setApiKey($supplier_token);

// Step two: Set the role for a user
$this->helper->setRole(GuardianRole::SUPPLIER);

// With a $project json 
$project = [ 'field0' => \Ramsey\Uuid\Uuid::uuid4(), 'field1' => 'data' ];

$result = (object) $this->helper->createProject($project);

$project_uuid = json_decode($project, true)['field0'];

/**
 * Waiting query for the registry to scan for the newly created project
 */
$waiting_query = EntityStateWaitingQuery::instance()
    ->query(StateQuery::PROJECTS)
    ->status(EntityStatus::WAITING)
    ->filter($project_uuid);

// Step four: approve the  through the standard registry
$result = (object) GuardianSDKHelper::actions(
    fn () => $this->helper->accessTokenForRegistry(),
    fn ($token) => $this->helper->setApiKey($token),
    fn () => $this->helper->stateEntityListener($waiting_query),
    fn ($query) => $this->helper->approveProject($query->id)
)();

```

## V2 Example Flow

Run this following test to understand how the Guardian SDK Helper can aid in simpler development of consuming the Guardian.

```
 ./vendor/bin/pest --filter ElvGuardianPolicyIntegrationTest
```

The class primarily is a range of decorator helper functions but there a number of core features that are helpful.

Namely:

- StateEntityListener
- Actions

The _StateEntityListener_ expects a _EntityStateWaitingQuery_ this will continually scan the guardian for an expected state after an action has occurred.

So, if you have created a "project" document, you can automate the scanning of guardian state for a "project" that is in the "waiting" state to be approved. As the guardian has alot of side-effects it can be slow to finalise state when compared to a regular REST API. 

The _Actions_ from the helper allows you to chain composable units logic together so, as above the action states.

- Retrieve the access, token for the registry
- Set the API key
- Create a waiting query to scan for expected state.
- Approve the project Using the results from the query.

**Recommendation:** As the Guardian platform is asynchronous, and you cannot necessarily know when data will be available. We recommend that UUIDs are set ahead of time within documents, this gives you a unique value that ensures you can scan for a unique entity (at DOVU our approach is to set this within field0 or uuid fields).   

## V1 Example Flow (Deprecated)

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
