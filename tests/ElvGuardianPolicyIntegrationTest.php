<?php

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Constants\StateQuery;
use Dovu\GuardianPhpSdk\DovuGuardianAPI;
use Dovu\GuardianPhpSdk\Support\EntityStateWaitingQuery;
use Dovu\GuardianPhpSdk\Support\GuardianSDKHelper;
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
        "field0" => Uuid::uuid4(),
        "field1" => "CARBON_REDUCTION",
        "field3" => "Illum commodi quidem dolorem voluptatibus.",
        "field4" => "Porro qui error earum quia iure praesentium molestiae.",
        "field5" => "Aut necessitatibus voluptatem quae nemo reiciendis officia et aperiam quia.",
        "field6" => "Quia maiores vel et reprehenderit eius fugiat quae nihil.",
        "field7" => "Aliquid et sint sint assumenda nostrum eum.",
        "field8" => "Quia explicabo dolorum minima perspiciatis suscipit odit explicabo aut amet.",
        "field9" => [ "Field 9" ],
    ]),
]);

dataset('site', [
    json_encode([
        "field0" => Uuid::uuid4(),
        "field1" => "Name of Site",
        "field2" => "Address of site",
        "field3" => "Name of POC",
        "field5" => "Number of POC",
        "field4" => "[0,0]",
    ]),
]);

dataset('claim', [
    json_encode([
        "field0" => Uuid::uuid4(),
        "field1" => "1233",
        "field2" => "IPFS",
        "field3" => "1.2",
        "field6" => 2023,
        "field4" => [
            "field0" => [
                "field0" => 1,
                "field1" => 2,
                "field2" => 3,
                "field3" => 3,
                "field4" => 3,
                "field5" => 3,
                "field6" => 3,
                "field7" => 3,
                "field8" => 3,
                "field9" => 3,
            ],
            "field1" => [
                "field0" => "1",
                "field1" => 2,
                "field2" => "Registration No.",
                "field3" => "3",
                "field4" => "Certificate of Deposit",
                "field5" => "4",
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
        $sdk = new DovuGuardianAPI();
        $sdk->setGuardianBaseUrl($sdk->config['app']['base_url']);

        $this->helper = new GuardianSDKHelper($sdk);
    });

    it('The Standard Registry can login (test credentials)', function () {
        expect($this->helper->accessTokenForRegistry())->toBeTruthy();
    })->skip("Using test creds: username = 'dovuauthority', password = 'test'");

    it('The Supplier can login (test credentials)', function () {
        expect($this->helper->accessTokenForSupplier())->toBeTruthy();
    })->skip("Using test creds: username = 'supplier', password = 'test'");

    // We might have to check for specific exceptions
    it('The Supplier cannot login', function () {
        $this->helper->accessTokenForSupplier('bad');
    })->throws(Error::class)->skip("Using bad creds");

    /**
     * As a supplier I want to submit a project:
     *  1. check that a supplier cannot see "create_projects" step (this is for registry)
     *  2. send a payload to "create a project", (a.) bad payload (b.) good payload
     *  3. approve project through a registry
     */

    it('[Project] The supplier can query state', function () {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryState(StateQuery::CREATE_SITE)
        )();

        expect($result->status_code)->toBe(200);
        expect($result->reason)->toBe("OK");

        // This will be more than 0 if a project has been approved.
        // This proves that the count exists and is a number.
        expect($result->count)->toBeGreaterThanOrEqual(0);
    })->skip('Example of querying previous state for "creating a site"');

    it('[Project] The supplier cannot query projects', function () {
        $result = GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryState(StateQuery::PROJECTS)
        )();

        expect($result)->toThrow(UnauthorizedException::class);
    })->skip("Example that proves that different roles can only query certain data.");

    it('[Project] The supplier cannot submit a bad project, with an incorrect schema', function () {
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->createProject('[]')
        )();

        $validation = (object) $result->errors['error'];

        expect($validation->statusCode)->toBe(422);
    })->skip('Validation checking for initial doc/project submission1');

    it('[Project] The supplier can submit a project', function ($project) {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->createProject($project)
        )();

        expect($result->status_code)->toBe(200);
    })->skip("Example of submitting a new project through a supplier");

    it('[Project] The registry cannot query create site step', function () {
        $result = GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryState(StateQuery::CREATE_SITE)
        )();

        expect($result)->toThrow(UnauthorizedException::class);
    })->skip("Example that only a supplier can view data related to creating a site.");

    it('[Project] The registry can approve a project and return the first "waiting" state, or check approved is valid', function () {
        $query = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryWaiting(StateQuery::PROJECTS)
        )();

        if ($query->count) {

            /**
             * NOTICE: This would usually be ok for a regular policy, If there are many projects connected to a policy, you can dig to
             * the "StateService" and use the "filters" method.
             **/
            $id = $query->result[0]['id'];

            expect($id)->toBeTruthy();

            // This is ok as the "SDK" context has already been assigned to standard registry auth
            $result = $this->helper->approveProject($id);

            expect($result->status_code)->toBe(200);

            return;
        }

        // This path checks for projects that have been approved, to reuse tests.
        $approvedProjects = (object) $this->helper->queryApproved(StateQuery::PROJECTS);

        expect(! ! $approvedProjects->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a registry approving a project for a supplier");

    /**
     * As a supplier I want to submit a site from a project:
     *  1. check that a supplier can see "create_site" step
     *  2. send a payload to "create_site", (a.) bad payload (not working) (b.) good payload
     *  3. approve site through a registry
     *  4. check that the site has been approved through a registry
     */

    it('[Site] Check that a supplier can view "create_site" state', function () {
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_SITE)
        )();

        expect($result->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a supplier checking their created projects, before creating a site.");

    //    TODO: This is broken as guardian schema block validation isn't working.
    it('[Site] Send a bad payload from a supplier to "create_site"', function () {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_SITE),
            fn ($query) => $this->helper->createSite($query->result[0]['id'], [ 'a' => 1 ])
        )();

        $validation = (object) $result->errors['error'];

        expect($validation->statusCode)->toBe(422);
    })->skip('Validation example for sending wrong document/data to create site');

    it('[Site] Send a good payload from a supplier to "create_site"', function ($_project, $site) {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_SITE),
            fn ($query) => $this->helper->createSite($query->result[0]['id'], $site)
        )();

        expect($result->status_code)->toBe(200);
    })->skip("Example of sending new create site document.");

    it('[Site] Approve a new "create_site" from registry', function () {
        $query = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryWaiting(StateQuery::APPROVE_SITE)
        )();

        if ($query->count) {

            /**
             * NOTICE: This would usually be filtered, If there are many sites connected to a policy, you can dig to
             * the "StateService" and use the "filters" method.
             *
             * We tend to recommend the first element (field0) of a "document submission"
             * is an uuid that is generated from a client for simple tracking
             **/
            $id = $query->result[0]['id'];

            expect($id)->toBeTruthy();

            // This is ok as the "SDK" context has already been assigned to standard registry auth
            $result = $this->helper->approveSite($id);

            // Should be 409 if the site/block data has already been approved
            expect($result->status_code)->toBe(200);

            return;
        }

        // This path checks for projects that have been approved, to reuse tests.
        $approvedProjects = (object) $this->helper->queryApproved(StateQuery::APPROVE_SITE);

        expect(! ! $approvedProjects->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a registry approving a site from a supplier");

    it('[Site] Check that a registry can view "approve_claim" state to get last site', function () {
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::APPROVE_SITE)
        )();

        expect($result->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a registry checking approved sites.");


    /**
     * As a supplier I want to create a claim (MRV) of an asset to a site:
     *  1. check that a supplier can see "create_claim" step
     *  2. send a payload to "create_claim", (a.) bad payload (not working) (b.) good payload
     *  3. approve claim through a verifier
     *  4. check that the claim has been approved through a verifier
     *  5. check the token has been minted?
     */

    it('[Claim] Check that a supplier can view "create_claim" state', function () {
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_CLAIM)
        )();

        expect($result->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a supplier reading create claim data, getting related sites.");

    //    TODO: This is broken as block validation isn't working.
    it('[Claim] Send a bad payload from a supplier to "create_claim"', function () {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_CLAIM),
            fn ($query) => $this->helper->createClaim($query->result[0]['id'], [ 'a' => 1 ])
        )();

        $validation = (object) $result->errors['error'];

        expect($validation->statusCode)->toBe(422);
    })->skip('Validation example for sending wrong document/data to create claim');

    it('[Claim] Send a good payload from a supplier to "create_claim"', function ($_project, $_site, $claim) {

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryApproved(StateQuery::CREATE_SITE),
            fn ($query) => $this->helper->createClaim($query->result[0]['id'], $claim)
        )();

        expect($result->status_code)->toBe(200);
    })->skip("Example of a supplier creating a claim");

    it('[Claim] Approve a new "create_claim" from verifier', function () {
        $query = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForVerifier(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->queryWaiting(StateQuery::APPROVE_CLAIM)
        )();

        if ($query->count) {

            $id = $query->result[0]['id'];

            expect($id)->toBeTruthy();

            // This is ok as the "SDK" context has already been assigned to standard registry auth
            $result = (object) $this->helper->approveClaim($id);

            expect($result->status_code)->toBe(200);

            return;
        }

        // This path checks for projects that have been approved, to reuse tests.
        $approvedProjects = (object) $this->helper->queryApproved(StateQuery::APPROVE_CLAIM);

        expect(! ! $approvedProjects->count)->toBeGreaterThanOrEqual(1);
    })->skip("Example of a verifier approving a claim");

    it('[Long Running] A user can be created', function () {

        $username = 'dovu_' . rand();

        // We use this to create different users of different roles.
        $registrant = $this->helper->createNewUser($username, 'secret');

        $user = $registrant['data'];
        $user_token = $user['accessToken'];
        $user_did = $user['did'];

        expect($user['username'])->toBe($username);
        expect($user_did)->toBeString();
        expect($user['role'])->toBe('USER');
        expect($user_token)->toBeString();

    })->skip('Example account creation: Long Running Task');


    /*********** End-to-End test below that covers the entire flow ********/

    it("This is the end-to-end test from registrant account creation to minting", function ($project, $site, $claim) {

        $username = 'dovu_' . rand();        $registrant = $this->helper->createNewUser($username, 'secret');

        $supplier_token = $registrant['data']['accessToken'];

        $this->helper->setApiKey($supplier_token);

        // Step two: Set the role for a user
        $this->helper->setRole(GuardianRole::SUPPLIER);

        ray("Username: $username for supplier");

        // We use this to create different users of different roles.


        ray('Setting role for Supplier');

        // TODO need to listen for state changes here
        sleep(20);

        ray('Attempting to create project');

        // Step three: Upload the initial document for new project
        $result = (object) $this->helper->createProject($project);

        expect($result->status_code)->toBe(200);

        $project_uuid = json_decode($project, true)['field0'];

        /**
         * Waiting query for the registry to scan for the newly created project
         */
        $waiting_query = EntityStateWaitingQuery::instance()
            ->query(StateQuery::PROJECTS)
            ->status(EntityStatus::WAITING)
            ->filter($project_uuid);

        // Step four: approve a document through the standard registry
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->stateEntityListener($waiting_query),
            fn ($query) => $this->helper->approveProject($query->id)
        )();

        expect($result->status_code)->toBe(200);

        /**
         * Waiting query for the supplier to create a site from an approved project
         */
        $waiting_query = EntityStateWaitingQuery::instance()
            ->query(StateQuery::CREATE_SITE)
            ->status(EntityStatus::APPROVED)
            ->filter($project_uuid);

        // Step five: Upload the site for approval as a registrant
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier($username),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->stateEntityListener($waiting_query),
            fn ($query) => $this->helper->createSite($query->id, $site)
        )();

        expect($result->status_code)->toBe(200);

        $site_uuid = json_decode($site, true)['field0'];

        /**
         * Waiting query for the registry to approve a site from an approved project
         */
        $waiting_query = EntityStateWaitingQuery::instance()
            ->query(StateQuery::APPROVE_SITE)
            ->status(EntityStatus::WAITING)
            ->filter($site_uuid);

        //step six: approve the ecological project
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForRegistry(),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->stateEntityListener($waiting_query),
            fn ($query) => $this->helper->approveSite($query->id)
        )();

        expect($result->status_code)->toBe(200);

        /**
         * Waiting query for the supplier to create a claim from an approved site
         */
        $waiting_query = EntityStateWaitingQuery::instance()
            ->query(StateQuery::CREATE_CLAIM)
            ->status(EntityStatus::APPROVED)
            ->filter($site_uuid);

        //step seven: send claim for project;
        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForSupplier($username),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->stateEntityListener($waiting_query),
            fn ($query) => $this->helper->createClaim($query->id, $claim)
        )();

        expect($result->status_code)->toBe(200);

        //step eight: create verifier
        $verifier_user = 'verifier_' . rand();
        $verifier = $this->helper->createNewUser($verifier_user, 'secret');

        $verifier_token = $verifier['data']['accessToken'];

        // Might tidy.
        $this->helper->setApiKey($verifier_token);

        $this->helper->setRole(GuardianRole::VERIFIER);

        // This will eventually be a waiting query to check the verifier exists
        sleep(20);

        $claim_uuid = json_decode($claim, true)['field0'];

        /**
         * Waiting query for the verifier to approve a claim from a supplier
         */
        $waiting_query = EntityStateWaitingQuery::instance()
            ->query(StateQuery::APPROVE_CLAIM)
            ->status(EntityStatus::WAITING)
            ->filter($claim_uuid);

        $result = (object) GuardianSDKHelper::actions(
            fn () => $this->helper->accessTokenForVerifier($verifier_user),
            fn ($token) => $this->helper->setApiKey($token),
            fn () => $this->helper->stateEntityListener($waiting_query),
            fn ($query) => $this->helper->approveClaim($query->id)
        )();

        expect($result->status_code)->toBe(200);
    });


})->with('project', 'site', 'claim');
