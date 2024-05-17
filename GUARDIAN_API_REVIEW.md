# Evaluating the Hedera Guardian API: Focusing on end-to-end Dry Run Functionality

## Introduction

**Note**: While this review aims to be as thorough as possible in detailing the end-to-end workflow using the Guardian API's dry run capability, it is important to acknowledge that many of these issues are still workable. Our primary focus is to eliminate any data-related "N+1" issues and to enable filtering on blocks without requiring policy modifications, thus providing a robust foundational piece for future development.

The Hedera Guardian represents a significant advancement in decentralized identity and data management. As we continue to integrate and optimize its capabilities, it's essential to approach our evaluation with a spirit of collaboration and mutual support. This document aims to provide a comprehensive overview of our latest experience with the Guardian API, focusing on shared challenges and the collective journey towards scalable and efficient solutions.

We will also be including links to the SDK As it is being built, and in addition, links to all related Github issues on Guardian.

_Also, If you would like to see any (loom) videos That illustrate any item within this document please let me know, and I'll be more than happy to deliver_

## Current issues listed on Github related to this dry-run review

1. [Filtering data for blocks is stateful API, introduce stateless data filters for API usage.](https://github.com/hashgraph/guardian/issues/3610)
2. [[Dry run API] Creation of new users returns all users](https://github.com/hashgraph/guardian/issues/3642)
3. [Refreshing of available filter state on Guardian (Potential Caching Issue)](https://github.com/hashgraph/guardian/issues/3641)

Here is the link to the [PHP SDK for Guardian](https://github.com/dovuofficial/guardian-php-sdk/blob/research/guardian-client/) for reference. 

## Initial API Evaluation: Utilizing the Guardian's Dry Run Capability

**Objective**
The primary objective of this initial runthrough is to leverage the Guardian's "Dry Run" capability. This will help us understand the minimum viable flow required to push data through all the necessary blocks to mint a potential environmental asset.

**Focus Areas**
1. **Minimum Viable Flow**:
    - Identify the essential steps and interactions needed to successfully navigate the Guardian API.
    - Ensure that data can be pushed through all blocks, culminating in the minting of an environmental asset.

2. **Exclusion of Hedera Network Interactions**:
    - At this stage, we are not focused on interacting with the Hedera network itself.
    - Specifically, we are not concerned with the creation of accounts or identities on a testnet environment.

3. **Documentation and Process**:
    - Document the current state of the API usage in the trust chain.
    - Ensure the process is digestible and scalable for future implementations.

**Approach**

By utilizing the "Dry Run" capability, we aim to simulate the entire process without committing to any actual transactions on the Hedera network. This allows us to refine our understanding of the Guardian API's requirements and capabilities in a controlled environment.

**Manual Testing Processes**

To test the policy effectively, we are manually undertaking the following processes:

1. **Local Deployment**:
    - Using Docker Compose to deploy the Guardian software locally.

2. **User Creation**:
    - Creating a standard registry user, referred to as "dovuauthority".

3. **Policy Import**:
    - Manually importing the policy, specifically the End of Life (ELV) policy used in production for MMCM.

These steps will be automated in the future. However, for now, all tests are dependent on these manual processes.

## PHP SDK Development

**Purpose**
Develop a PHP library to interface with the Guardian, enabling developers familiar with PHP or Laravel environments to interact with the Guardian API effectively.

**Core Functionality**
Implemented through a test suite using "Pest."

**Supporting Helper Classes**
1. **DryRunScenario.php**:
    - Manages operations related to executing "dry runs" of policy interactions.

2. **GuardianSDKHelper.php**:
    - Contains helper functions and utilities that simplify interactions with the Guardian API.

3. **PolicyContext.php**:
    - Sets up the necessary environment and parameters for interacting with a specific policy.

4. **PolicyMode.php**:
    - Defines different modes or configurations for policy execution.

5. **PolicyWorkflow.php**:
    - Manages the workflow of policy operations, ensuring all steps are executed correctly.

6. **DryRunService.php**:
    - Manages operations related to executing "dry runs."

7. **AccountService.php**:
    - Handles account-related operations within the Guardian ecosystem.

8. **AbstractServiceFactory.php**:
    - Factory class used to instantiate service objects.

9. **AbstractService.php**:
    - Base class for all service classes, promoting code reuse and reducing duplication.

10. **MrvService.php**:
- Handles operations related to the Measurement, Reporting, and Verification (MRV) process.

11. **BlockService.php**:
- Manages operations related to policy blocks.

12. **StateService.php**:
- Deals with the state management of various entities within the Guardian system.

13. **ServiceFactory.php**:
- Provides concrete implementations for creating instances of various service classes.

14. **PolicyService.php**:
- Handles operations related to policy management.

15. **EntityStateWaitingQuery.php**:
- Defines a query class for handling states of entities that are in a "waiting" status.

## N+1 Problem in Dry Run User Creation

**Understanding the N+1 Problem**

The N+1 problem arises when, for every new object created, an additional object is included in the response of a subsequent GET request, leading to inefficiency and performance issues. In the context of the Guardian API, this problem is evident during the creation of new users in a dry run scenario due to the lack of pagination.

**Dry Run User Creation**

During the dry run, each time a new user is created, the system returns every single user that exists in the policy. This is demonstrated by the following process:

```php
$updated_users = $this->dry_run_scenario->createUser();
```

## The N+1 Issue

The createUser function not only creates a new user but also retrieves all existing users in the policy. This results in an increasing number of users returned as more users are created. Specifically, for each new user created, the number of users returned grows, leading to a significant performance bottleneck. For example:

- Creating the first user returns 1 user.
- Creating the second user returns 2 users.
- Creating the third user returns 3 users.
- And so on...

## Impact on Scalability

This N+1 problem presents a substantial challenge for scaling tests, particularly when aiming to test the software with hundreds of thousands of users. As the number of users increases, the system's performance degrades due to the escalating volume of data being returned with each GET request.

## Challenges in Working with the Guardian API

**Understanding the Guardian's Inner Workings**

Working with the Guardian API involves a deep understanding of its inner workings, often requiring a combination of reverse engineering and navigating vague documentation. The current state of the API presents several challenges that developers must overcome to effectively use the system.

**User Creation and Role Assignment**

During a "dry run," a user must be created without interfacing with the Hedera network for environments like testnet. This can be done using the following method in the SDK:

Assigning a role to this user is managed through the SDK:

```php
$role = $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);
```

The implementation of this method looks like:

```php
public function assignRole(string $policyId, GuardianRole $role)
{
    $data = [
        'role' => $role->value,
    ];

    /**
     * The boolean value from the server is returned as an object, mapping to scalar. We undo this.
     *
     * TODO: Consider building a HTTP response DTO.
     */
    return $this->sendToTag($policyId, 'choose_role', $data)->scalar;
}
```

Here, there are a few key points to consider:

- The method assumes that the tag name in the policy is always "choose_role," which may not be standardized across different policies.
- For DOVU-specific policies, this works, but it cannot be universally trusted due to the lack of standardization in policy creation.

## Documentation and Reverse Engineering

The documentation for sending data to a block is quite vague: Hedera Guardian Documentation (link)

The Guardian backend relies on specific endpoints, making it difficult to understand what data should be pushed at any given time. The method for sending data looks like this:

```php
public function sendToTag(string $policyId, string $tag, $data): object
{
    return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", (array) $data, true);
}
```

For specific contexts, such as choosing a role, developers often need to use the Guardian UI and inspect the network tab to understand the logs and actions being performed. The Guardian's backend uses a "block ID" approach, but tagging, which is more user-friendly, remains undocumented. This requires developers to infer information from both official documentation and reverse engineering efforts.

## Practical Example: Submitting Data to a Policy

An example of submitting data to a policy involves hardcoding a dataset and injecting a UUID into the payload to ensure downstream control. This is demonstrated with a project submission:

```php
dataset('project', [
    json_encode([
        "uuid" => Uuid::uuid4(),
        "field0" => "Sustainable End of Life Vehicle Scrapping Program",
        "field1" => "This is completed through digitizing of blended UN e-waste methodology (AMS-III.BA) and UN Recovery and recycling of materials from solid wastes (AMS-III.AJ) and applying it to end-of-life vehicles for tracking emission avoidance. Introducing a new unit type for selling credits to enhance market transparency and traceability. These units, termed ELV Credit, represents the environmental impact of processing each End of Life Vehicle (ELV) through Government Authorized Vehicle Scrapping Centers, which are termed Registered Vehicle Scrapping Facility (RVSFs) in India.",
        "field2" => "CARBON_REDUCTION",
        "field3" => "India",
        "field4" => "Technological Emission Avoidance",
        "field5" => "UNFCCC Third Party Verified Blended Methodologies: AMS-III.BA.: Recovery and recycling of materials from E-waste (v3.0) &AMS-III.AJ: Recovery and recycling of materials from solid wastes (v7.0)",
        "field6" => "01 August 2022",
        "field7" => [ "https://cdm.unfccc.int/methodologies/DB/TO0E8JPL9361FDB1IPF0TUPS0WJXV3", "https://cdm.unfccc.int/methodologies/DB/R22750M155F84YR0D4YVYOS0CLSCII" ],
    ]),
]);
```

Sending the document to the appropriate tag is straightforward:

```php
$project = json_decode($project, true);
$uuid = $project['uuid'];
$tag = "create_ecological_project";
$this->policy_workflow->sendDocumentToTag($tag, $project);
```

However, issues arise if the tag name is incorrect, as the Guardian API's response may be null, indicating the request was not processed. This necessitates careful validation of sent data and subsequent filtering to ensure state retention.

# Policy Approval and Filtering

## Workflow Context

Assuming the initial actor, called the supplier, has submitted data into the policy, the next steps involve a registry/superuser or potential VVB actor who must approve the project.

## Steps to Approve a Project

- Within the test case, examples use timeouts specifically "sleep". Previously, code was created to listen for when Guardian is ready to accept the next stage of a workflow, as it has reached a state. Despite not using external services such as IPFS or Hedera, there is still an asynchronous issue.
- To address this, a UUID is generated to pluck specific blocks, ensuring downstream services have control over the data pushed into the Guardian.
- Introduced "filterAddon" blocks to mitigate the N+1 problems without pagination or filtering, which could result in a slow system due to large data blocks.
- Cache invalidation issues require pushing data to a block, requesting the data belonging to a particular tag (triggering a cache refresh), filtering on a specific value, and then requesting the data again related to a particular block.

## Filtering Process

- Push Data: Push data to a block by tag or ID.
- Cache Refresh: Ask for the data that belongs to a particular tag, triggering a cache refresh in the Guardian.
- Filter Data: Use the filter block to enable filtering on a particular value.
- Retrieve Data: Request the data again related to a particular block.

## Challenges with Filtering

- Filtering any kind of data needs a sequence of steps to ensure correct data processing.
- There are N+1 issues in filter responses, as it returns all filters available for every single object in a particular block, which is not suitable for large datasets.

Example code snippet:

```php
public function filterByTag(string $policyId, string $tag, string $uuid): object
{
    // Trigger a cache refresh by requesting data by tag
    $this->dataByTag($policyId, $tag);

    // Apply filter to the tag
    return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", [
        'filterValue' => $uuid,
    ], true);
}
```

## Understanding Approval Steps

In the Guardian workflow, certain blocks of data submitted by an actor with the role of "Supplier" need to be approved by another actor with the role of "Verifier" or "Registry" before the workflow can proceed. This section outlines the steps required to perform these approval actions and the associated challenges.

**Example Code for Approval Steps**

```php
/**
 * As the "Administrator" filter and fetch the valid block
 */
// As standard authority (first in the list of dry run users)
$admin = $users[0]['did'];

$this->dry_run_scenario->login($admin);

$this->policy_workflow->filterByTag("supplier_grid_filter", $uuid);
$supplier = $this->policy_workflow->dataByTagToDocumentBlock("supplier_grid");

/**
 * Ensure that the expected uuid matches the filter
 */
expect($supplier->uuid)->toBe($uuid);

/**
 * Ensure that the expected status matches state
 */
expect($supplier->getStatus())->toBe(EntityStatus::WAITING->value);

/**
 * 7. With the button submit the project approval as an administrator
 */
$supplier->updateStatus(EntityStatus::APPROVED->value);

$option_tag = GuardianApprovalOption::APPROVE->value;
$supplier->assignTag($option_tag);

/**
 * Ensure that the expected status matches state before registry submission
 */
expect($supplier->getStatus())->toBe(EntityStatus::APPROVED->value);
expect($supplier->getTag())->toBe($option_tag);

$tag = "approve_supplier_btn";
$this->policy_workflow->sendDataToTag($tag, $supplier->forDocumentSubmission());
```

## Explanation of Steps

1. Administrator Login and Filtering:
  - The administrator (or the first user in the list of dry run users) logs in.
  - Using the filterByTag method, the administrator filters the data block associated with the "supplier_grid_filter" tag and retrieves the supplier data block.
  - The UUID of the retrieved block is checked to ensure it matches the expected UUID.
  - The status of the retrieved block is verified to be in the "WAITING" state.

2. Approval Submission:
  - The administrator updates the status of the supplier block to "APPROVED".
  - The appropriate approval option tag is assigned to the supplier block.
  - The status and tag of the supplier block are verified again before submission.
  - The updated supplier block is submitted to the "approve_supplier_btn" tag.

### Data Payload for Approval Submission

When approving a data block that represents a project, it is necessary to log in as the correct user and submit a payload that includes the updated data and the appropriate tag. An example payload looks like this:

Data Payload for Approval Submission

When approving a data block that represents a project, it is necessary to log in as the correct user and submit a payload that includes the updated data and the appropriate tag. An example payload looks like this:

```json
{
  "document": {
    // Old data with new status
  },
  "tag": "Option_0" // This relates to the tagged, but an option that belongs specifically to the button with tag "approve_supplier_btn"
}
```

Updating Block Status

The status of the block is updated using the following method, which hardcodes the values within the policy itself:

```php
public function updateStatus(string $status)
{
    $this->block_data['option']['status'] = $status;
}
```

Challenges

1. Reverse Engineering:
 - Due to the lack of comprehensive documentation and examples, a significant amount of reverse engineering is required to understand the exact tags and the necessary payload structure. This process can take weeks or months to fully implement an end-to-end flow with a policy on the API.

2. Black Box Testing:
 - The process of understanding and implementing the approval steps is often a black box. Developers need to experiment and test various scenarios to determine the correct workflow, making the process time-consuming and complex.

By addressing these challenges and improving documentation, the Guardian API can be made more accessible and easier to work with, reducing the time required to implement and test approval workflows.

## Chaining Blocks through Policy Process

### Introduction

Chaining blocks through the policy process is a crucial aspect of interacting with the Guardian API. This involves connecting different stages of a workflow by referring to previous blocks, ensuring that data flows seamlessly from one block to another.

### Example Scenario

Consider a scenario where a block representing a "project registration/definition" for a project developer or supplier has just been approved. An entity with a particular role, such as a registry or verifier (VVB), takes a positive action to approve this block.

Following this, another block related to the supplier needs to connect to the first block. For instance, this could be a "site" block, representing a geographical area or sensor installation linked to the project.

### Code Example for Chaining Blocks

```php
$supplier = $this->policy_workflow->dataByTagToDocumentBlock("create_site_form");

expect($supplier->getStatus())->toBe(EntityStatus::APPROVED->value);

/**
 * Prepare site document
 */
$site = json_decode($site, true);
$uuid = $site['uuid'];

// As the supplier user from before.
$this->dry_run_scenario->login($user->did);

/**
 * Send site document to the correct tag using previous doc as reference.
 */
$tag = "create_site_form";
$referred_doc = $supplier->chainDocumentAsReference($site);

$this->policy_workflow->sendDataToTag($tag, $referred_doc);
```

## Explanation of the Code

1. Fetching Current Data:
 - The dataByTagToDocumentBlock method fetches the current data for the tag "create_site_form."
 - The status of the fetched data is verified to ensure it is in the "APPROVED" state.

2. Preparing the Site Document:
 - The site document is prepared and its UUID is extracted.

3. Logging in as the Supplier:
 - The supplier logs in to continue the process.

4. Sending Data to the Correct Tag:
- The chainDocumentAsReference method is used to create a reference to the previous document.
- The new document, along with the reference, is sent to the "create_site_form" tag.

## Chaining Documents with References

The "chainDocumentAsReference" method is essential for linking the new document to the previous block:

```php
public function chainDocumentAsReference($document): array
{
    return [
        'document' => $document,
        'tag' => $this->tag,
        'ref' => $this->block_data,
    ];
}
```

## Practical Considerations

1. Extracting and Referencing Data:
   - The block data from the initial fetch needs to resolve to the given reference.
   - The correct tag and any new document that has been validated are injected into the document array.

2. Handling Asynchronous Operations:
   - Timeouts, such as sleep, are used to handle asynchronous operations and ensure that the Guardian is ready to accept the next stage of the workflow.
   - Despite not using external services such as IPFS or Hedera, there are still asynchronous issues that need to be managed.

3. Complex Scenarios:
   - In scenarios where a site has been created and claims need to be assigned against that site, the process can be more complex. The following example illustrates this:

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

4. Data Extraction and Workflow Inconsistencies:
- The data extraction and reference process may vary between different parts of the workflow. For example, fetching initial references from "sites_grid" and then sending data to "create_claim_request_form" does not follow a consistent pattern.
- This inconsistency can lead to confusion and difficulty in achieving the desired results across different contexts.

5. Cache Invalidation and State Management:
- Cache invalidation issues require pushing data to a block, requesting the data belonging to a particular tag (triggering a cache refresh), filtering on a specific value, and then requesting the data again related to a particular block.
- The Guardian API's stateful nature can cause problems when multiple processes are acting in parallel.

6. Effort and Documentation:
- Due to vague documentation, significant effort is required to fully understand and implement the workflow through the API.
- The process often involves trial and error and reverse engineering, making it time-consuming and complex.

## Summary of Challenges

Summary of Challenges

- Reverse Engineering: The lack of comprehensive documentation and examples necessitates a significant amount of reverse engineering to understand the exact tags and necessary payload structures.
- Black Box Testing: Implementing approval steps often requires experimenting and testing various scenarios to determine the correct workflow.
- Inconsistencies in Data Handling: Different parts of the workflow may require different methods to achieve the same results, leading to potential confusion and errors.
- Managing Asynchronous Operations: Handling stateful interactions and asynchronous operations can be challenging, especially without external services like IPFS or Hedera.
- Cache Invalidation: Ensuring data consistency and managing cache invalidation is crucial for maintaining the correct workflow state.

By addressing these factual challenges, developers can better navigate the complexities of chaining blocks through the policy process in the Guardian API. Black Box Testing: Implementing approval steps often requires experimenting and testing various scenarios to determine the correct workflow.
