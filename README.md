# Guardian PHP SDK

Configuration based Guardian policy consumption and management.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dovuofficial/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/dovuofficial/guardian-php-sdk)
[![Tests](https://github.com/dovuofficial/guardian-php-sdk/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/dovuofficial/guardian-php-sdk/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/dovuofficial/guardian-php-sdk.svg?style=flat-square)](https://packagist.org/packages/dovuofficial/guardian-php-sdk)


## Installation

You can install the package via composer:

```bash
composer require dovuofficial/guardian-php-sdk
```

## Current Work in Progress

This is our third iteration of creating an SDK to wrap around the Guardian. We are unsure at this time, whether we will create a new repository or simply just version this current repo.

The goal here is to have a direct connection to the Guardian API services itself and really think about designing a PHP SDK from the ground up.

There are a number of items that need continued work in the next couple of weeks to have very refined, but simple SDK.

-[x] Minimise methods required to interface with guardian. 
-[x] Using processes/templates as a mechanism to work with guardian.
- Ongoing documentation of how to use system
- Validation of the structure of a guardian workflow element, based on assumptions.
- [x] Update primary test to ensure that a trustchain can be read through API (possible caching issue)
- Update primary test to ensure that the import process for a policy takes place.
- Update primary test to use "listeners" for waiting for a particular state to be ready
  - Ensure that this call is integrated into a given "workflow" action seemlessly
- Add feature and test suite to support "testnet" tests after dry run, requirements:
  - Ability to create new testnet users with account ids/key
  - Assign roles
  - Ensure the listener functionality for this pass (we don't know how long IPFS/Hedera calls will take)
- Update GuardianActionTransaction to check for roles, as a _verbose_ mode.
- Update core tests (Dryrun/Testnet) to ensure that a "standard registry" is created from scratch
  - The use case here is, that if we provision a new guardian infrastructure the test suite needs to run all tasks e2e from zero state.
- (In Progress) Ensure that we have solid tests in place that highlights current guardian challenges, especially when it comes to scalability concerns specifically around N+1 issues for data query

## These tasks will be added after the e2e raw flow

- Modification of core workflow to allow custom schemas for any stage
- Output of new configuration files for particular usecases

## These are tasks that have been deferred and classed as low priority

- (Deferred) Ability to ingest any guardian policy and then dynamically infer all tags, roles, and order of operations as well as schemas.

## The current strategic approach

As there is little to no documentation on how to correctly use the Guardian API in a production/scale, setting, we need to gather insight into how we can force the Guardian to be more restful by default.

In short, we are currently using a process whereby we inject "filterBlocks" Into specific areas into the policy all areas of data, we wish to query. This is rather loaded, but in short, if there is a particular block on the workflow, we want to query like a site location for a project we can do so by creating a filter ahead of time.

Currently, there are still a number of N +1 issues within the Guardian. This means that currently there is still work to be done to ensure that we would be happy for production, we need a foundational piece of software that does the simple things right.

This version of the SDK is certainly almost thought through/optimal over the last three years, and it is our intent to use this system.

## Using the SDK, see tests.

This SDK uses a combination of a mediator and strategy pattern to be able to ingest and process configuration related to a particular workflow template.

For a client consuming this SDK with some configuration this is the expected flow:

1) Setup the initial context objects (this can be simplified)
2) Import a "GuardianWorkflowConfiguration" from a filename.
3) Generate a specification, which should be imported into your system.
4) Elements of the specification will be used when processing each stage of a workflow.


The creation of a context object with a supplied policy happens in this manner.

```php
$this->sdk = new DovuGuardianAPI();
$this->sdk->setGuardianBaseUrl("http://localhost:3000/api/v1/");

$context = PolicyContext::using($this->sdk)->for($policy_id);
$this->policy_workflow = PolicyWorkflow::context($context);

/**
 * Set up the workflow from configuration
 */
$configuration = $this->policy_workflow->getConfiguration();
$conf = GuardianWorkflowConfiguration::get('test_workflow'); // From the "/config" folder
$specification = $configuration->generateWorkflowSpecification($conf['workflow']);
```

The _specification_ is an array that contains all elements that are a hard requirement that needs to be passed into a "GuardianActionTransaction" further more a "schema_specification" is added which allows systems to validate payloads before hitting a Guardian system.  

```php
    [
      0 => array:4 [▶
        "role" => Dovu\GuardianPhpSdk\Constants\GuardianRole {#93▶}
        "tag" => "create_ecological_project"
        "type" => Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask {#710▶}
        "schema_specification" => array:13 [▶
          "title" => "ELV Scrapping for CO2 Emission Avoidance (AMS-III.BA & AMS-III.AJ)"
          "description" => "End-of-life vehicle project registration for the recovery and recycling of materials from e-waste, using a digitised form of UN CDM Methodology version 3.0"
          "type" => "object"
          "required" => array:12 [▶]
          "uuid" => array:4 [▶]
          "field0" => array:4 [▶]
          "field1" => array:4 [▶]
          "field2" => array:4 [▶]
          "field3" => array:4 [▶]
          "field4" => array:4 [▶]
          "field5" => array:4 [▶]
          "field6" => array:4 [▶]
          "field7" => array:4 [▶]
        ]
      ]
    ]
```
_There will be more validation added so the fields of each element can be checked accordingly_

## The mediator object, that allows the consumption of elements

This can either be stored in state or generated every time a new transaction is created.

```php
/**
 * Create mediator object.
 */
$mediator = GuardianActionMediator::with($this->policy_workflow);
```

## Using a WorkflowElement, consuming the workflow in real time.

Now, the onus is on the system outside of guardian to the consumption of a policy.

If we consider different stages of how it should work, here are some examples below.

```php
/**
 * Stage one: create an ecological project (identity handled outside)
 */
$users = $this->dry_run_scenario->createUser(); // Returns a list of all users
$user = (object) end($users);
$this->dry_run_scenario->login($user->did);
$this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

/**
 * Build an object for the particular action
 */
$send_ecological = (object) $specification[0];
$element = WorkflowElement::parse($send_ecological);
$project = json_decode($project, true); // See "ResearchElvClientGuardianTest"

$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setPayload($project)
    ->run();
```

_Note:_ The "$project" Can be either a JSON string or an array/dictionary, Eventually, we will add validation that will ensure that the given payload matches the expectation for a particular workflow element in realtime.

Within the tests, or within your work, you can add logging statements ([ray](https://myray.app/) is used here) -- the timeout/sleep function will be changed with state listener functionality.

```php
ray('$send_ecological');
ray($result);

// TODO: Use the listener logic (This will increase based off of the current resource load on API)
sleep(2);
```

The next stage of the flow would be to handle the _approval of a "project"_.

```php
/**
 * Stage two: login as registry (handled outside workflow)
 */
$this->dry_run_scenario->login($admin->did);

$approve_ecological = (object) $specification[1];
$element = WorkflowElement::parse($approve_ecological);

// TODO: This would be the "plucker" (can we make this more dynamic?)
$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setFilterValue($project['uuid'])
    ->setApprovalOption(ApprovalOption::APPROVE)
    ->run();
```

In this, next state/stage, the original "Supplier" Now has access to creates a site that is connected to a project.

```php
/**
 * Stage three: login as supplier for site creation (handled outside workflow)
 */
$this->dry_run_scenario->login($user->did);

$create_site = (object) $specification[2];
$element = WorkflowElement::parse($create_site);

$site = json_decode($site, true);

$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setPayload($site)
    ->run();
```

And like before an admin/registry who is logged in can now approve the site.

```php
/**
 * Stage four: login as registry for site approval (handled outside workflow)
 */
$this->dry_run_scenario->login($admin->did);

$approve_site = (object) $specification[3];
$element = WorkflowElement::parse($approve_site);

$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setApprovalOption(ApprovalOption::APPROVE)
    ->setFilterValue($site['uuid'])
    ->run();
```

So, now a "supplier" can now issue claims Against a site where there is proof of some impact activity.

```php
/**
 * Stage five: login as supplier for claim creation (handled outside workflow)
 */
$this->dry_run_scenario->login($user->did);

$create_claim = (object) $specification[4];
$element = WorkflowElement::parse($create_claim);

$claim = json_decode($claim, true);

$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setPayload($claim)
    ->setFilterValue($site['uuid'])
    ->run();
```

Finally, one must get access to a verifier, a trusted authority, that can sign off or approve on a given claim, which will then issue a token. 

```php
/**
 * Stage six: create a verifier user
 */

// Create verifier
$users = $this->dry_run_scenario->createUser(); // Returns a list of all users
$verifier = (object) end($users);

// Assign role
$this->dry_run_scenario->login($verifier->did);
$this->policy_workflow->assignRole(GuardianRole::VERIFIER);

/**
 * Stage seven: login as verifier for claim approval (handled outside workflow)
 */
$this->dry_run_scenario->login($verifier->did);

$approve_claim = (object) $specification[5];
$element = WorkflowElement::parse($approve_claim);

$result = GuardianActionTransaction::with($mediator)
    ->setWorkflowElement($element)
    ->setApprovalOption(ApprovalOption::APPROVE)
    ->setFilterValue($claim['uuid'])
    ->run();
```

From this time forward, a trustchain will be generated that will also issue digital credits with all of this information embed.

## Implications of workflow and templates.

This template relies on our specific "DOVU Standard" and has traditionally followed IWA flows.

The benefit of this approach is that any workflow configuration can be adjusted or created to align closer to the needs of specific centralised registries or other entities.

As an example, for Verra There are expectations that a verifier/VVB must review and approve the project or the PDD, instead of the registry itself -- And that's the issuance of tokens it's not dependent on constant verification, but that or external Actors are happy with the current state of the project itself, and that they are happy to issue ongoing credits post project validation.

So, with this in mind, all the system needs to manage is the ability for any configuration to be used in a particular structure where these use cases are fully formed from within guardian, After these tests have been successful, then the same workflow template can be used to branch off into any kind of methodology (Through the injection of schemas that are "n" levels deep) for a specific registry. These methodologies can either be generated through the use of AI Systems and can be further verified for validity from the registry authorities.

## Understanding the GuardianActionTransaction object.

The **GuardianActionTransaction** Object is focused on ensuring the simplest way to process a guardian policy, by providing a single builder class that can be configured for a particular context, or in other words whether something needs to be posted to a "policy block" or if a "block" needs to be approved.

The ongoing work will include validation to whether for a particular workflow action enough information has been provided, as an ongoing task it needs to be more _defensive_ to check against bad states and input automatically (perhaps as a "verbose" mode)

Below are the breakdown of each individual method Within the builder and how they should be used for each portion of the test workflow.

### The base object

This snippet below produces a base transaction connected to any workflow element.

```php
GuardianActionTransaction::with($mediator)->setWorkflowElement($element)
```

### Action Transaction Methods

The `setPayload` Method is used to connect a particular payload to a transaction, this would be used in the submission of data to a particular item/block in the workflow.

The `setFilterValue` can Be used for any action that requires some kind of filter, From the approval of a particular entity, or the submission of identity, where there are many entities before it like a claim connecting to a site.

The `setApprovalOption` should only be used in the approval/denial of an entity in a workflow.

The `run` method take all the information provided and attempts to process it as a guardian action (TODO: We will be adding validation to ensure that for a particular action, or the elements have been submitted that the payloads are valid).

# Trustchain and audit of token provenance

After assets have been issued through the garden, you make use the Trustchain class to return all of the Information that can be used to create a visual on outsiders. To see the entire flow of how something was created with all actors.

For the provenance of assets, where on network, there is a serial number connected to an asset, you can match a unique identifier that you previous added to a policy instance, like a uuid inside of a "claim" schema.

Please look at the "TokenAuditTrail" test for more information

## How it works (low level)

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

While all of these code snippets seem relatively simple, alot of thought has gone into them, there are a few additions that need work, this is a work in progress inside of the testcase "ResearchElvClientGuardianTest".  

## V2 Example Documentation (Deprecated)

_This SDK lets you perform API calls to Dovu's Guardian API middleware. Making working with Hedera Guardian more streamlined._

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
