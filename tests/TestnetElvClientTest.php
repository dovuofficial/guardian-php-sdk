<?php

use Dovu\GuardianPhpSdk\Config\EnvConfig;
use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\Env;
use Dovu\GuardianPhpSdk\Constants\GuardianApprovalOption;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\HederaAccount;
use Dovu\GuardianPhpSdk\Domain\PolicySchemaDocument;
use Dovu\GuardianPhpSdk\Domain\UserAccount;
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
        "field8" => "test",
        "field9" => "FIRST_OPTION",
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
        "field3" => "1", // This can change for token issuance.
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

/**
 * Downstream clients can create their own tests.
 */
describe('Functional Guardian Test', function () {
    beforeEach(function () {
        $config = EnvConfig::instance();

        $this->sdk = new DovuGuardianAPI();
        $this->sdk->setGuardianBaseUrl($config->get(Env::GUARDIAN_API_URL));

        $policy_id = $config->get(Env::POLICY_ID);

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

    /**
     * This test to create a new user for **published** policies, so testnet/mainnet.
     *
     * Below should be used as a reference to how to manage a user lifecycle and to attach to a particular role.
     *
     * The new "management" of roles is handled through a "registry" user toward a particular user.
     */
    it('A new testnet user can be registered with a supplier role.', function () {

        // Prepare initial usernames and configuration
        $config = EnvConfig::instance();
        $username = Uuid::uuid4();
        $registry = $config->get(Env::STANDARD_REGISTRY_USERNAME);
        $policy_id = $config->get(Env::POLICY_ID);

        // Testing for logging into on Guardian UI
        ray($username);

        // Initial register action and expectations
        $register = $this->sdk->accounts->register($username, '123456', GuardianRole::USER);

        expect($register->username)->toBeTruthy();
        expect($register->permissions)->toBeTruthy();
        expect($register->permissionsGroup)->toBeTruthy();
        expect($register->permissionsGroup[0]["roleName"])->toBe("Default policy user");

        // There is a constant switch between different users/roles
        // **It describes an automated flow within the system**
        $this->helper->authenticateAsRegistry($registry);
        $session = $this->sdk->accounts->session();
        $registry_did = $session->did;

        // This next section is the final signup page through register when connecting to a registry
        $this->helper->authenticateAsActor($username);
        $this->actor_facade->assignAccountToRegistry($username, $registry_did);

        // As a registry you must give a user the permission to a use a policy
        $this->helper->authenticateAsRegistry($registry);
        $this->sdk->policies->assign($username, $policy_id);

        // Finally assign a role to the actor, this case a supplier.
        $this->helper->authenticateAsActor($username);
        $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

        // Expect that the actor has the correct role in policy.
        $state = $this->policy_workflow->getPolicy();
        expect($state->userRole)->toBe(GuardianRole::SUPPLIER->value);

    })->skip();

    /**
     * Verbose run through so that every actor is created on the fly.
     */
    it('Using SDK builder methods to create registry, import, navigate and process the entire testnet flow.', function ($project, $site, $claim) {

        /***
         * Complete run through, regardless of resource waste for new users.
         */
        $config = EnvConfig::instance();
        $registry_user = $config->get(Env::STANDARD_REGISTRY_USERNAME);
        $password = $config->get(Env::STANDARD_REGISTRY_PASSWORD);
        $policy_id = $config->get(Env::POLICY_ID);

        $this->helper->authenticateAsRegistry($registry_user, $password);

        // Stop here for pre-checks
        expect($policy_id)->toBeTruthy();
        expect($registry_user)->toBeTruthy();
        expect($password)->toBeTruthy();

        /**
         * Set up the workflow from configuration
         */
        $conf = GuardianWorkflowConfiguration::prepare('test_workflow');
        $configuration = $this->policy_workflow->getConfiguration();
        $specification = $configuration->generateWorkflowSpecification($conf->workflow);

        // The assumption here is that the policy would already be in a "published" state
        // $this->policy_mode->publish();

        /**
         * Create mediator object, to enable Guardian Actions.
         */
        $mediator = GuardianActionMediator::with($this->policy_workflow);

        // Creation of a "project developer" or "supplier" user (see test above for full expectations)
        ray("Creation of 'project developer'");

        $username = Uuid::uuid4();
        $this->sdk->accounts->register($username, '123456', GuardianRole::USER);

        $this->helper->authenticateAsRegistry($registry_user);
        $session = $this->sdk->accounts->session();
        $registry_did = $session->did;

        $this->helper->authenticateAsActor($username);
        $this->actor_facade->assignAccountToRegistry($username, $registry_did);

        $this->helper->authenticateAsRegistry($registry_user);
        $this->sdk->policies->assign($username, $policy_id);

        $this->helper->authenticateAsActor($username);
        $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

        ray("Completed 'project developer' actor - $username");

        // Finish "Project Developer" setup

        /**
         * Stage one: create an ecological project (identity handled outside)
         */
        $send_ecological = (object) $specification[0];
        $element = WorkflowElement::parse($send_ecological);
        $project = json_decode($project, true);

        $result = GuardianActionTransaction::with($mediator)
            ->setWorkflowElement($element)
            ->setPayload($project)
            ->run();

        ray('$send_ecological -- testnet');
        ray($result);

        expect($result)->toBeTruthy();

        /**
         * Stage two: login as registry (handled outside workflow)
         */
        $this->helper->authenticateAsRegistry();

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
        $this->helper->authenticateAsActor($username);

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

        expect($result)->toBeTruthy();

        /**
         * Stage four: login as registry for site approval (handled outside workflow)
         */
        $this->helper->authenticateAsRegistry();

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
        $this->helper->authenticateAsActor($username);

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

        expect($result)->toBeTruthy();

        /**
         * Stage six: create a verifier user
         */
        // Creation of a "Verifier" or "vvb" user
        ray("Creation of 'verifier'");

        $verifier = Uuid::uuid4();
        $this->sdk->accounts->register($verifier, '123456', GuardianRole::USER);

        $this->helper->authenticateAsActor($verifier);
        $this->actor_facade->assignAccountToRegistry($verifier, $registry_did);

        $this->helper->authenticateAsRegistry($registry_user);
        $this->sdk->policies->assign($verifier, $policy_id);

        $this->helper->authenticateAsActor($verifier);
        $this->policy_workflow->assignRole(GuardianRole::VERIFIER);

        ray("Completed 'verifier' actor - $verifier");
        // Finish "Verifier" setup

        /**
         * Stage seven: login as verifier for claim approval (handled outside workflow)
         */
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

    });
})
    ->with('project', 'site', 'claim');
