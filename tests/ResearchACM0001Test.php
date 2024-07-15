<?php

use Dovu\GuardianPhpSdk\Config\EnvConfig;
use Dovu\GuardianPhpSdk\Constants\Env;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\DovuGuardianAPI;
use Dovu\GuardianPhpSdk\Support\DryRunScenario;
use Dovu\GuardianPhpSdk\Support\GuardianActorFacade;
use Dovu\GuardianPhpSdk\Support\GuardianSDKHelper;
use Dovu\GuardianPhpSdk\Support\PolicyContext;
use Dovu\GuardianPhpSdk\Support\PolicyMode;
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

dataset('participant', [

]);

dataset('vvb', [

]);

dataset('project', [

]);

dataset('report', [

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

    it('Using SDK builder methods to create registry, import, navigate and process the entire dryrun flow.', function ($participant, $vvb, $project, $report) {


        /***
         * This below is simply using a user that already exists in the system
         * to currently save on testnet HBARs that are transferred to a new
         * account, in prod/testnet this would be part of the process.
         */

        $config = EnvConfig::instance();
        $has_registry = $config->hasStandardRegistry();

        $registry_user = $has_registry ? $config->get(Env::STANDARD_REGISTRY_USERNAME) : "registry:" . Uuid::uuid4();
        $password = $has_registry ? $config->get(Env::STANDARD_REGISTRY_PASSWORD) : '123456';

        if (! $has_registry) {
            $register = $this->actor_facade->newRegistryAccount($registry_user, $password);

            ray("new registry user");
            ray($register);

            $this->helper->authenticateAsRegistry($registry_user, $password);

            $hedera_account = $this->actor_facade->generateDemoKey();
            $task = $this->actor_facade->addHederaAccountToActor($registry_user, $hedera_account);

            ray("addHederaAccountToActor");
            ray($task);
        }


        $this->helper->authenticateAsRegistry($registry_user, $password);

        /**
         * Set up the workflow from configuration
         */
        $conf = GuardianWorkflowConfiguration::prepare('acm0001_workflow');

        ray($conf);

        $policy_id = $config->testLocalPolicy();

        if (! $policy_id) {

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

            ray('policy_workflow->context->import->fromTimestamp');
            ray($task);

            $policy_id = $task->result["policyId"];
        }

        expect($policy_id)->toBeTruthy();

        $context = PolicyContext::using($this->sdk)->for($policy_id);

        $this->helper = new GuardianSDKHelper($this->sdk, $policy_id);

        // Create policy and dry run helpers
        $this->policy_mode = PolicyMode::context($context);
        $this->dry_run_scenario = DryRunScenario::context($context);
        $this->policy_workflow = PolicyWorkflow::context($context);
        $this->actor_facade = GuardianActorFacade::context($context);

        $configuration = $this->policy_workflow->getConfiguration();


        $specification = $configuration->generateWorkflowSpecification($conf->workflow);

        ray($specification);

        return;

        //        return ray($specification);

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
         *
         * TODO: this is a "dry-run" scenario -- so this would need to be changed for a testnet user
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

        ray('$approve_ecological');
        ray($result);

        /**
         * Stage three: login as supplier for site creation (handled outside workflow)
         */
        $this->dry_run_scenario->login($user->did);

        $create_site = (object) $specification[2];
        $element = WorkflowElement::parse($create_site);

        $site = json_decode($site, true);

        ray('Attempt $create_site');

        $result = GuardianActionTransaction::with($mediator)
            ->setWorkflowElement($element)
            ->setPayload($site)
            ->run();

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

        ray('$approve_claim');
        ray($result);

        // We should be able to read the trustchain.

    });//->skip();

})->with('project', 'site', 'claim');
