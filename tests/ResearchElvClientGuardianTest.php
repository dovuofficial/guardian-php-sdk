<?php

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\DovuGuardianAPI;
use Dovu\GuardianPhpSdk\Support\DryRunScenario;
use Dovu\GuardianPhpSdk\Support\GuardianSDKHelper;
use Dovu\GuardianPhpSdk\Support\PolicyContext;
use Dovu\GuardianPhpSdk\Support\PolicyMode;
use Dovu\GuardianPhpSdk\Support\PolicyStatus;
use Dovu\GuardianPhpSdk\Support\PolicyWorkflow;
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
//        "field3" => "Name of POC",
//        "field5" => "Number of POC",
//        "field4" => "[0,0]",
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

        // mmcm elv
        $policy_id = "661d52b7e641c9bef4f65566";

        $context = PolicyContext::using($this->sdk)->for($policy_id);

        $this->helper = new GuardianSDKHelper($this->sdk, $policy_id);

        // Create policy and dry run helpers
        $this->policy_mode = PolicyMode::context($context);
        $this->dry_run_scenario = DryRunScenario::context($context);
        $this->policy_workflow = PolicyWorkflow::context($context);
    });

    it('The Standard Registry can read the session', function () {

        $token = $this->helper->accessTokenForRegistry();

        $this->sdk->setApiToken($token);

        $session = $this->sdk->accounts->session();

        expect($session->status_code)->toBeTruthy();
        expect($session->reason)->toBeTruthy();
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
    })->skip(); // Done.

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

        ray($register);

        expect($register->username)->toBeTruthy();
        expect($register->password)->toBeTruthy();
        expect($register->did)->toBeFalsy();
        expect($register->role)->toBe(GuardianRole::USER->value);

        $login = $this->sdk->accounts->login($username, '123456');

        ray($login);

    })->skip();


    it('A policy can be toggled between dry run and draft modes', function () {

        $token = $this->helper->accessTokenForRegistry();

        $this->helper->setApiKey($token);

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRAFT))->toBeTruthy();

        $this->policy_mode->dryRun();

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRY_RUN))->toBeTruthy();

        $this->policy_mode->draft();

        expect($this->policy_mode->hasPolicyStatus(PolicyStatus::DRAFT))->toBeTruthy();

    })->skip();

    it('A policy can create users and view them in dry run', function () {

        $token = $this->helper->accessTokenForRegistry();

        $this->helper->setApiKey($token);

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

        $token = $this->helper->accessTokenForRegistry();

        $this->helper->setApiKey($token);

        // TODO: This is inconsistent due to guardian HTTP timeout issues
        $this->policy_mode->dryRun();

        // TODO: Uncomment this to restart dry run state before assertions (in case of errors)
        // $this->dry_run_scenario->restart();

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

        $this->policy_mode->draft();
    })->skip();

    it('A dry-run policy can create user, with a role, and submit project data', function ($project) {

        $token = $this->helper->accessTokenForRegistry();

        $this->helper->setApiKey($token);

        // TODO: This is inconsistent due to guardian HTTP timeout issues
        $this->policy_mode->dryRun();

        // TODO: Uncomment this to restart dry run state before assertions (in case of errors)
        //         $this->dry_run_scenario->restart();

        $updated_users = $this->dry_run_scenario->createUser();

        $user = (object) end($updated_users);

        $this->dry_run_scenario->login($user->did);

        $this->policy_workflow->assignRole(GuardianRole::SUPPLIER);

        // Send data to block
        // These tags will be referenced as constants
        $tag = "create_ecological_project";

        $document = json_decode($project, true);

        $project_response = $this->policy_workflow->sendDocumentToTag($tag, $document);

        ray($project_response);

        ray($updated_users);

        $this->dry_run_scenario->login($updated_users[0]['did']);

        ray($this->policy_workflow->dataByTag("supplier_grid"));



        //        $this->dry_run_scenario->restart();

        //        $this->policy_mode->draft();
    });//->skip();



})->with('project', 'site', 'claim');
