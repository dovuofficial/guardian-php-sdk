<?php

use Dovu\GuardianPhpSdk\Config\EnvConfig;
use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\Env;
use Dovu\GuardianPhpSdk\Constants\GuardianApprovalOption;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\PolicySchemaDocument;
use Dovu\GuardianPhpSdk\DovuGuardianAPI;
use Dovu\GuardianPhpSdk\Support\DryRunScenario;
use Dovu\GuardianPhpSdk\Support\GuardianActorFacade;
use Dovu\GuardianPhpSdk\Support\GuardianSDKHelper;
use Dovu\GuardianPhpSdk\Support\PolicyContext;
use Dovu\GuardianPhpSdk\Support\PolicyMode;
use Dovu\GuardianPhpSdk\Support\PolicyStatus;
use Dovu\GuardianPhpSdk\Support\PolicyWorkflow;
use Dovu\GuardianPhpSdk\Workflow\Constants\ApprovalOption;
use Dovu\GuardianPhpSdk\Workflow\GuardianActionMediator;
use Dovu\GuardianPhpSdk\Workflow\GuardianWorkflowConfiguration;
use Dovu\GuardianPhpSdk\Workflow\Model\GuardianActionTransaction;
use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowElement;
use Ramsey\Uuid\Uuid;

/********************************************* OVERVIEW *************************************************
 * This test suite is designed to demonstrate and validate key functionalities of the DOVU Guardian SDK.
 * It includes:
 *
 * 1) An end-to-end test showcasing the complete process from user creation, through policy creation,
 *    to the final minting step. This test provides a comprehensive view of the SDK's workflow.
 *
 * 2) Individual tests marked with a "skip" status. These serve as templates for developing custom
 *    test cases. They include hints and guidelines to assist in understanding and extension.
 *
 * Overall, the suite aims to offer both a holistic understanding of the SDK's capabilities and
 * specific insights into each component's functionality. It complements our Gitbook documentation,
 * providing developers with practical, code-based examples and additional technical details.
 *******************************************************************************************************/

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

dataset('site', [
    json_encode([
        "uuid" => Uuid::uuid4(),
        "field0" => "MTC BUSINESS PRIVATE LIMITED",
        "field1" => "PLOT NO - 559, BOL GIDC, SANAND - 2 IND ESTATE, SANAND, 382111",
        "field2" => "Name of POC",
        "field3" => "Number of POC",
        "field4" => "[0,0]",
    ]),
]);

dataset('claim', [
    json_encode([
        "uuid" => Uuid::uuid4(),
        "field0" => "Certificate of Deposit",
        "field1" => "COD2023062PB10DN1161",
        "field2" => "https://cloudflare-ipfs.com/ipfs/bafybeiafql4r5xn6nyuamktltmjnklapiyck5w6mtpx7pragvhtr56iase/COD1161.pdf",
        "field3" => "1.58735",
        "field4" => 2023,
        "field5" => [
            "field0" => [
                "field0" => 1.802,
                "field1" => 0.1855,
                "field2" => 0.0265,
                "field3" => 0.265,
                "field4" => 0.0795,
                "field5" => 0.1325,
                "field6" => 0,
                "field7" => 0.0795,
                "field8" => 0,
            ],
            "field1" => [
                "field0" => "Registration Number",
                "field1" => "PB10DN1161",
                "field2" => "LMV/Motor Car",
                "field3" => "2650",
                "field4" => "Others",
                "field5" => "Diesel",
            ],
        ],
    ]),
]);

/********************************** FUTURE SCOPE *************************************
 * Future Enhancements for Dynamic Testing:
 *
 * This test suite sets the foundation for advanced, dynamic testing capabilities. In the future,
 * clients will have the ability to inject their own schemas at different stages of the workflow.
 * This feature will enable clients to:
 *
 * 1) Test with both seeded data and their own custom data, ensuring flexibility and adaptability.
 * 2) Validate if the policy can be successfully deployed within their specific context.
 * 3) Confirm that the data being processed aligns with their expectations and requirements.
 *
 * This progression towards dynamic testing will enhance the SDK's utility, making it more
 * versatile and user-friendly. It will allow clients to not just test but also tailor the
 * system to their unique environmental and ecological credit scenarios.
 **************************************************************************************/

describe('Functional Guardian Test', function () {
    beforeEach(function () {
        $this->sdk = new DovuGuardianAPI();
        $this->sdk->setGuardianBaseUrl("http://localhost:3000/api/v1/");

        // TODO: Remove. mmcm elv
        $policy_id = "667ae92ef14d4f12d4382242";

        $context = PolicyContext::using($this->sdk)->for($policy_id);

        $this->helper = new GuardianSDKHelper($this->sdk, $policy_id);

        // TODO: Remove. Create policy and dry run helpers (shouldn't be here)
        $this->policy_mode = PolicyMode::context($context);
        $this->dry_run_scenario = DryRunScenario::context($context);
        $this->policy_workflow = PolicyWorkflow::context($context);
        $this->actor_facade = GuardianActorFacade::context($context);
    });

    it('Tests should be enabled', function () {
        expect(EnvConfig::instance()->testsEnabled())->toBeTruthy();
    });

    it('The Standard Registry can read the session', function () {

        $token = $this->helper->accessTokenForRegistry();

        $this->sdk->setApiToken($token);

        $session = $this->sdk->accounts->session();

        expect($session->_id)->toBeTruthy();
        expect($session->id)->toBeTruthy();
        expect($session->createDate)->toBeTruthy();
        expect($session->updateDate)->toBeTruthy();
        expect($session->username)->toBeTruthy();
        expect($session->password)->toBeTruthy();
        // TODO: This can fail with no DID (account id)
        expect($session->did)->toBeTruthy();
        expect($session->parent)->toBeNull();
        expect($session->walletToken)->toBe('');
        expect($session->role)->toBe(GuardianRole::REGISTRY->value);
        expect($session->refreshToken)->toBeTruthy();
    })->skip();

    /**
     * TODO: Ensure valid HTTP Status codes for conflict of current user.
     */
    it('A user cannot be registered', function () {

        // 'test' -> 123456
        $register = $this->sdk->accounts->register('dovuauthority', '123456', GuardianRole::REGISTRY);

        /**
         * This is a test that has invalid API behaviour
         *
         * 409 should be used instead of 500
         */

        ray($register);
    })->skip();

    it('A new user can be registered', function () {

        $username = Uuid::uuid4();

        $register = $this->sdk->accounts->register($username, '123456', GuardianRole::USER);

        expect($register->username)->toBeTruthy();
        expect($register->password)->toBeTruthy();
        expect($register->did)->toBeFalsy();
        expect($register->role)->toBe(GuardianRole::USER->value);
    });


    it('A policy can be toggled between dry run and draft modes', function () {

        $this->helper->authenticateAsRegistry();

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRAFT))->toBeTruthy();

        $this->policy_mode->dryRun();

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRY_RUN))->toBeTruthy();

        $this->policy_mode->draft();

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRAFT))->toBeTruthy();

    })->skip();

    it('A policy can create users and view them in dry run', function () {

        $this->helper->authenticateAsRegistry();

        // TODO: This is inconsistent due to guardian HTTP timeout issues
        $this->policy_mode->dryRun();

        $users = $this->dry_run_scenario->users();

        expect(count($users))->toBe(1);

        $user = $this->dry_run_scenario->createUser();

        // TODO: ISSUE -  Feels wrong as a creation of a user should return one element
        expect(count($user))->toBe(2);

        $this->dry_run_scenario->restart();

        $users = $this->dry_run_scenario->users();

        expect(count($users))->toBe(1);

        $this->policy_mode->draft();

    })->skip();

    it('A dry-run policy can create user and assign a role', function () {

        $this->helper->authenticateAsRegistry();

        // TODO: This is inconsistent due to guardian HTTP timeout issues
        $this->policy_mode->dryRun();

        // TODO: Uncomment this to restart dry run state before assertions (in case of errors)
        $this->dry_run_scenario->restart();

        $users = $this->dry_run_scenario->users();

        expect(count($users))->toBe(1);

        $updated_users = $this->dry_run_scenario->createUser();

        $user = (object) end($updated_users);

        $this->dry_run_scenario->login($user->did);

        $role = $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

        expect($role)->toBeTruthy();

        $state = $this->dry_run_scenario->policyState();

        expect($state->userRole)->toBe(GuardianRole::SUPPLIER->value);

        $this->dry_run_scenario->restart();

        //        $this->policy_mode->draft();
    })->skip();

    // TODO: Deprecated (old style)
    it('A dry-run policy after status change can expect data for a site', function ($project, $site, $claim) {

        /**
         * 1. Authenticate as registry
         */
        $this->helper->authenticateAsRegistry();

        /**
         * 2. Ensure dry run and (possible) restart state
         */
        //                $this->policy_mode->dryRun();
        //        $this->dry_run_scenario->restart();

        /**
         * 3. Creating a new user in dry run state where a role is assigned.
         * TODO: Create class on user creation with a role.
         */
        $users = $this->dry_run_scenario->createUser(); // Returns a list of all users
        $user = (object) end($users);
        $this->dry_run_scenario->login($user->did);
        $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

        /**
         * 4. Prepare document
         */
        $project = json_decode($project, true);
        $uuid = $project['uuid'];

        /**
         * 5. Send document to the correct tag
         */
        $tag = "create_ecological_project";
        $this->policy_workflow->sendDocumentToTag($tag, $project);

        // TODO: Use the listener logic (This will increase based off of the current resource load on API)
        sleep(2);

        /**
         * 6. As the "Administrator" filter and fetch the valid block
         */
        // As standard authority (first in the list of dry run users)
        $admin = $users[0]['did'];

        $this->dry_run_scenario->login($admin);

        // This is stateful in API.
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

        sleep(2);

        $this->dry_run_scenario->login($user->did);

        $supplier = $this->policy_workflow->dataByTagToDocumentBlock("create_site_form");

        expect($supplier->getStatus())->toBe(EntityStatus::APPROVED->value);

        /**
         * 8. Prepare site document
         */
        $site = json_decode($site, true);
        $uuid = $site['uuid'];

        // As the supplier user from before.
        $this->dry_run_scenario->login($user->did);

        /**
         * 9. Send site document to the correct tag using previous doc as reference.
         */
        $tag = "create_site_form";
        $referred_doc = $supplier->chainDocumentAsReference($site);

        $this->policy_workflow->sendDataToTag($tag, $referred_doc);

        sleep(2);

        /**
         * 10. As the "Administrator" filter and fetch the valid block
         */
        // As standard authority (first in the list of dry run users -- admin)
        $this->dry_run_scenario->login($admin);

        // This is stateful in API.
        $this->policy_workflow->filterByTag("site_grid_owner_filter", $uuid);
        $site = $this->policy_workflow->dataByTagToDocumentBlock("approve_sites_grid");

        /**
         * Ensure that the expected uuid matches the filter
         */
        expect($site->uuid)->toBe($uuid);

        /**
         * Ensure that the expected status matches state
         */
        expect($site->getStatus())->toBe(EntityStatus::WAITING->value);

        /**
         * 10. As the "Administrator" approve the site
         */
        $site->updateStatus(EntityStatus::APPROVED->value);

        $option_tag = GuardianApprovalOption::APPROVE->value;
        $site->assignTag($option_tag);

        /**
         * Ensure that the expected status matches state before registry site approve
         */
        expect($site->getStatus())->toBe(EntityStatus::APPROVED->value);
        expect($site->getTag())->toBe($option_tag);

        $tag = "approve_site_button";

        $this->policy_workflow->sendDataToTag($tag, $site->forDocumentSubmission());

        $this->policy_workflow->filterByTag("site_grid_owner_filter", $uuid);

        $site = $this->policy_workflow->dataByTagToDocumentBlock("approve_sites_grid");

        expect($site->getStatus())->toBe(EntityStatus::APPROVED->value);

        sleep(2);

        /**
         * 11. As the "Supplier" create a new "claim" related to the site.
         */

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

        sleep(2);

        /**
         * 12. As a new "verifier" filter and fetch the valid claim block
         */

        // Create verifier
        $users = $this->dry_run_scenario->createUser(); // Returns a list of all users
        $verifier = (object) end($users);

        // Assign role
        $this->dry_run_scenario->login($verifier->did);
        $this->policy_workflow->assignRole(GuardianRole::VERIFIER);

        // This is stateful in API.
        $this->policy_workflow->filterByTag("claim_request_verifier_filter", $claim_uuid);

        $claim = $this->policy_workflow->dataByTagToDocumentBlock("claim_requests_grid(verifier)");

        /**
         * Ensure that the expected uuid matches the filter
         */
        expect($claim->uuid)->toBe($claim_uuid);

        /**
         * Ensure that the expected status matches state
         */
        expect($claim->getStatus())->toBe(EntityStatus::WAITING->value);

        /**
         * 13. As a new "verifier" approve the claim for minting
         */
        $claim->updateStatus(EntityStatus::APPROVED->value);

        $option_tag = GuardianApprovalOption::APPROVE->value;
        $claim->assignTag($option_tag);

        expect($claim->getStatus())->toBe(EntityStatus::APPROVED->value);
        expect($claim->getTag())->toBe($option_tag);

        $tag = "approve_claim_requests_btn";

        // TODO: this approval isn't working
        $this->policy_workflow->sendDataToTag($tag, $claim->forDocumentSubmission());

        sleep(2);

        $this->policy_workflow->filterByTag("claim_request_verifier_filter", $claim_uuid);
        $claim = $this->policy_workflow->dataByTagToDocumentBlock("claim_requests_grid(verifier)");

        ray($claim);

        expect($claim->getStatus())->toBe(EntityStatus::APPROVED->value);

        // TODO: asset should mint!
    })->skip();

    // TODO: in progress
    //    it('An admin can read the trust chain', function () {
    //
    //        $this->helper->authenticateAsRegistry();
    //
    //        // TODO: test that a supplier/role could access trustchain
    //        $uuid = "89325a37-eaf0-47d6-b0cc-6c01c3fcc408";
    //
    //        $data = $this->policy_workflow->trustchainForTokenMint($uuid);
    //
    //        $trustchain = new Trustchain($data);
    //
    //        // TODO: WORK IN PROGRESS
    //        ray($trustchain->format());
    //    })->skip();

    it('Fetch a specification of a schema for workflow import and validation', function () {

        $this->helper->authenticateAsRegistry();

        // Ids are fetched through a tag of a block
        $id = "#a753238f-d4f5-4849-9249-fa93a4cc6365";

        $schema = $this->policy_workflow->getSchemaForKey($id);

        $document = json_decode($schema->document, true);

        $policySchemaDocument = new PolicySchemaDocument($document);

        $spec = (object) $policySchemaDocument->schemaValidationSpecification();

        expect($spec->type)->toBe("object");
        expect($spec->uuid)->toBeTruthy();

    })->skip();

    it('Using SDK builder methods to create registry, import, navigate and process the entire dryrun flow.', function ($project, $site, $claim) {


        /***
         * This below is simply using a user that already exists in the system
         * to currently save on testnet HBARs that are transferred to a new
         * account, in prod/testnet this would be part of the process.
         */

        // Create new user
        //        $registry_user = "registry:" . Uuid::uuid4();

        $registry_user = 'registry:3b0af58b-b3ee-400f-98d4-279d8c57a548';
        $password = '123456';

        //        $this->helper->authenticateAsRegistry($registry_user, $password);

        //        return;

        //        $register = $this->actor_facade->newRegistryAccount($registry_user, $password);
        //
        //        ray($register);
        //
        //        $this->helper->authenticateAsRegistry($registry_user, $password);
        //
        ////        $env = EnvConfig::instance();
        ////        ray($env->get(Env::HEDERA_ACCOUNT_ID));
        //
        //        $hedera_account = $this->actor_facade->generateDemoKey();
        //
        //        ray($hedera_account);
        //
        //        $task = $this->actor_facade->addHederaAccountToActor($registry_user, $hedera_account);
        //
        //        ray($task);

        $this->helper->authenticateAsRegistry($registry_user, $password);

        /**
         * Set up the workflow from configuration
         */
        $conf = GuardianWorkflowConfiguration::prepare('test_workflow');

        /*
         * Using timestamp value from "test_workflow" that is the concrete implementation
         * of a given workflow template
         */
        $timestamp = $conf->timestamp();

        expect($timestamp)->toBeTruthy();

        // When something happens, complete
        $status_update_callable = function ($state) {
            ray("Status update");
            ray($state);

            if ($state->result) {
                ray("done");
                ray($state->result);

                expect($state->result["policyId"])->toBeTruthy();
            }
        };

        $task = $this->policy_workflow->context->import->fromTimestamp($status_update_callable, $timestamp);

        ray($task);

        $policy_id = $task->result["policyId"];

        // TODO: remove, context "used on matt's machine" (to comment out import flow)
        //        $policy_id = "6682a6c3f14d4f12d4382494";

        $context = PolicyContext::using($this->sdk)->for($policy_id);

        $this->helper = new GuardianSDKHelper($this->sdk, $policy_id);

        // Create policy and dry run helpers
        $this->policy_mode = PolicyMode::context($context);
        $this->dry_run_scenario = DryRunScenario::context($context);
        $this->policy_workflow = PolicyWorkflow::context($context);
        $this->actor_facade = GuardianActorFacade::context($context);

        $configuration = $this->policy_workflow->getConfiguration();
        $specification = $configuration->generateWorkflowSpecification($conf->workflow);

        // Do the thing!
        $this->helper->authenticateAsRegistry($registry_user);

        /**
         * TODO: These are the tasks that need to be completed from state zero.
         * 1. Create a new "standard registry" flow (requires additional account creation methods)
         * 2. Import the policy configuration using the new standard registry
         * 3. Update the context objects with the correct uploaded "registry" user and imported policy id.
         */

        $this->policy_mode->dryRun();

        /**
         * Create mediator object.
         */
        $mediator = GuardianActionMediator::with($this->policy_workflow);

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
        $project = json_decode($project, true);

        $result = GuardianActionTransaction::with($mediator)
            ->setWorkflowElement($element)
            ->setPayload($project)
            ->run();

        ray('$send_ecological');
        ray($result);

        // TODO: Use the listener logic (This will increase based off of the current resource load on API)
        sleep(2);

        // Retain reference to admin
        // As standard authority (first in the list of dry run users)
        $admin = (object) $users[0];

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

        sleep(2);

        ray('$approve_ecological');
        ray($result);

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

        sleep(2);

        ray('$create_site');
        ray($result);

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

        sleep(2);

        ray('$approve_site');
        ray($result);

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

        sleep(2);

        ray('$create_claim');
        ray($result);

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

        sleep(2);

        ray('$approve_claim');
        ray($result);

        // We should be able to read the trustchain.

    });//->skip();

})->with('project', 'site', 'claim');
