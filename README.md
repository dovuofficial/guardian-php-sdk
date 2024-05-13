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

## Current Work in Progress

This is our third iteration of creating an SDK to wrap around the Guardian. We are unsure at this time, whether we will create a new repository or simply just version this current repo.

The goal here is to have a direct connection to the Guardian API services itself and really think about designing a PHP SDK from the ground up.

There are a number of items that need continued work in the next couple of weeks/months to have very refined, but simple SDK.

- Minimise methods required to interface with guardian.
- Using processes/templates as a mechanism to work with guardian.
- Ability to ingest any guardian policy and then dynamically infer all tags, roles, and order of operations as well as schemas.
- Ensure that we have solid tests in place that highlights current guardian challenges, especially when it comes to scalability concerns specifically around N+1 issues for data query

## The current strategic approach

As there is little to no documentation on how to correctly use the Guardian API in a production/scale, setting, we need to gather insight into how we can force the Guardian to be more restful by default.

In short, we are currently using a process whereby we inject "filterBlocks" Into specific areas into the policy all areas of data, we wish to query. This is rather loaded, but in short, if there is a particular block on the workflow, we want to query like a site location for a project we can do so by creating a filter ahead of time.

Currently, there are still a number of N +1 issues within the Guardian. This means that currently there is still work to be done to ensure that we would be happy for production, we need a foundational piece of software that does the simple things right.

This version of the SDK is certainly almost thought through/optimal over the last three years, and it is our intent to use this system.

## How it works

As the policies we have developed or completely outside the realms of any available documentation this is how the process works.

There are particular roles that can Interact with the system, for DOVU templates there are "Supplier" and "Verifier" roles, the superuser/admin/registry is also used.

Currently, we are focused on developing a dry run through to ensure we can work with the Envision team To highlights all potential bottlenecks for creating a foundational API, that should be able to scale, within reason.

Every user has to be assigned to a role in code it looks like this:

```php
$users = $this->dry_run_scenario->createUser();
$user = (object) end($users);
$this->dry_run_scenario->login($user->did);
$this->policy_workflow->assignRole(GuardianRole::SUPPLIER);
```

In this case, the "Supplier" can now send data or a document to the Policy that has previously been published:

```php
$project = json_decode($project, true);
$uuid = $project['uuid'];

$tag = "create_ecological_project";
$this->policy_workflow->sendDocumentToTag($tag, $project);
```

> One thing worth noting is that every single policy has potential to be completely unique, and while the team focus on the creation of a similar templates to help alleviate this problem, there is scope for complexity that needs to be built into this SDK, eventually meaning that any policy could potentially be traversed, where items can be plucked out for where data needs to be pushed to.

Next, an administrator/registry (Or potentially a VVB role) can login And filter all projects/applications related to methodology, After a review, this can be approved by this code below.

```php
$admin = $admin_did;

$this->dry_run_scenario->login($admin);

// This is stateful in API.
$this->policy_workflow->filterByTag("supplier_grid_filter", $uuid);
$supplier = $this->policy_workflow->dataByTagToDocumentBlock("supplier_grid");

$supplier->updateStatus(EntityStatus::APPROVED->value);
$option_tag = GuardianApprovalOption::APPROVE->value;

$supplier->assignTag($option_tag);

$tag = "approve_supplier_btn";
$this->policy_workflow->sendDataToTag($tag, $supplier->forDocumentSubmission());
```

The current "filterByTag" suffers from Issues, including cache that needs to be invalidated and N+1 Query problems for many documents that get added to the filter. 

After this step, the original supplier may start submitting sites, representing a geographical area, Related to a project, as the original project was approved by an actor/authority the supplier now has this capability.

```php
$site = json_decode($site, true);
$uuid = $site['uuid'];

// As the supplier user from before.
$this->dry_run_scenario->login($user->did);

$tag = "create_site_form";
$referred_doc = $supplier->chainDocumentAsReference($site);

$this->policy_workflow->sendDataToTag($tag, $referred_doc);
```

Please note that the current function "chainDocumentAsReference" Is a somewhat advanced quirk of the Guardian infrastructure itself as an order for the API system to understand what is going on. You need to provide the actual reference that links back to the previous block that you want to connect to. For many developers, this comprises of using the network tab within the browser to fully understand and reverse engineer the process completely.

The next stage is for the authority/registry to approve a provided site for a project (This is a similar process to how projects are initially approved).

```php
$this->dry_run_scenario->login($admin);

$this->policy_workflow->filterByTag("site_grid_owner_filter", $uuid);
$site = $this->policy_workflow->dataByTagToDocumentBlock("approve_sites_grid");

$site->updateStatus(EntityStatus::APPROVED->value);

$option_tag = GuardianApprovalOption::APPROVE->value;
$site->assignTag($option_tag);

$tag = "approve_site_button";

$this->policy_workflow->sendDataToTag($tag, $site->forDocumentSubmission());
```

From this point, a supplier can now submit a claim, this refers to the "dMRV" or data collection stage Of the process, this is not validated through a registry of self, but through a third-party actor, such as a verifier or some other proof that all parties agree on.

```php
$claim_doc = json_decode($claim, true);
$claim_uuid = $claim_doc['uuid'];

// As the supplier user from before.
$this->dry_run_scenario->login($user->did);

// Site uuid
$this->policy_workflow->filterByTag("site_grid_supplier_filter", $uuid);

$claim = $this->policy_workflow->dataByTagToDocumentBlock("sites_grid");

$tag = "create_claim_request_form";
$referred_doc = $claim->chainDocumentAsReference($claim_doc);

$this->policy_workflow->sendDataToTag($tag, $referred_doc);
```

For the final stage, a verifier can be created, all referenced previously through the DOVU System, And can be assigned to a particular policy, which they have the capability to verify the data and therefore Mint credits.

```php
// Create verifier
$users = $this->dry_run_scenario->createUser();
$verifier = (object) end($users);

// Assign role
$this->dry_run_scenario->login($verifier->did);
$this->policy_workflow->assignRole(GuardianRole::VERIFIER);

// This is stateful in API.
$this->policy_workflow->filterByTag("claim_request_verifier_filter", $claim_uuid);
$claim = $this->policy_workflow->dataByTagToDocumentBlock("claim_requests_grid(verifier)");

$claim->updateStatus(EntityStatus::APPROVED->value);
$option_tag = GuardianApprovalOption::APPROVE->value;
$claim->assignTag($option_tag);

$tag = "approve_claim_requests_btn";

// TODO: this approval isn't working
$this->policy_workflow->sendDataToTag($tag, $claim->forDocumentSubmission());
```

Why are all of these code snippets seem relatively simple, alot of thought has gone into them, there are a few additions that need work, this is a work in progress inside of the testcase "ResearchElvClientGuardianTest".  

## V2 Example Documentation (Deprecated)

This following documentation is the current deprecated methods and flow, we used by using a middleware API as a separate service that we would call over HTTPS/REST.

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
$project = [ 'uuid' => \Ramsey\Uuid\Uuid::uuid4(), 'field1' => 'data' ];

$result = (object) $this->helper->createProject($project);

$project_uuid = json_decode($project, true)['uuid'];

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
