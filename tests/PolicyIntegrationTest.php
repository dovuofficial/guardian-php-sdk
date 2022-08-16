<?php

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

dataset('registration', [
    json_encode([
        "field0" => "Blanditiis consequuntur repellat voluptatem quod et aperiam voluptas.",
        "field1" => "A at mollitia corporis molestiae ut debitis.",
        "field2" => 57779,
        "field3" => "Illum commodi quidem dolorem voluptatibus.",
        "field4" => "Porro qui error earum quia iure praesentium molestiae.",
        "field5" => "Aut necessitatibus voluptatem quae nemo reiciendis officia et aperiam quia.",
        "field6" => "Quia maiores vel et reprehenderit eius fugiat quae nihil.",
        "field7" => "Aliquid et sint sint assumenda nostrum eum.",
        "field8" => "Quia explicabo dolorum minima perspiciatis suscipit odit explicabo aut amet.",
    ]),
]);

dataset('ecological_project', [
    json_encode([
        "field0" => "1234",
        "field1" => "Matt's Farm",
        "field2" => "This is a description about Matt's farm",
        "field3" => "Matt Smithies",
        "field4" => [
            "field0" => "dovu.io",
            "field1" => "England",
            "field2" => "Micro",
        ],
        "field5" => [
            "field0" => "Same as above (field0 - Unique Identifier)",
            "field1" => "GeoJSON needed",
            "field2" => "Sequestion",
            "field3" => "Carbon Removal",
            "field4" => "N/A",
            "field5" => "N/A",
            "field6" => 0,
            "field7" => "N/A",
            "field8" => "Matt Smithies",
            "field9" => "DOVU",
            "field10" => "100",
        ],
    ]),
]);

dataset('agrecalc_mrv', [
    json_encode([
        "field0" => 100,
        "field1" => 100,
        "field2" => 100,
        "field3" => 100,
        "field4" => 100,
        "field5" => 100,
        "field6" => 100,
        "field7" => 100,
        "field8" => 100,
        "field9" => 100,
        "field10" => 100,
        "field11" => 100,
        "field12" => 100,
        "field13" => 100,
        "field14" => 100,
    ]),
]);

/**
 * Without going full PHPUnit it seems to be a challenge to move variables
 * between tests, in Laravel we'll rely on DB state between tests
 */
it('SDK can process a given policy', function ($registration, $ecological_project, $mrv) {
    $sdk = new DovuGuardianAPI();
    $sdk->setHmacSecret($sdk->config['local']['hmac_secret']);

    // Step One is generating a user.
    $username = 'dovu_' . rand();
    $response = $sdk->accounts->create($username, 'secret');

    $user = $response['data'];
    $user_token = $user['accessToken'];
    $user_did = $user['did'];

    expect($user['username'])->toBe($username);
    expect($user_did)->toBeString();
    expect($user['role'])->toBe('USER');
    expect($user_token)->toBeString();

    // We can use this throughout the processing of the policy.
    $policy_id = $sdk->config['local']['policy_id'];

    // Step two: Set the role for a user
    // Set the API token within the context of a user pre - registrant
    $sdk->setApiToken($user_token);

    // This feels odd that this response is empty.
    $response = $sdk->accounts->role($policy_id, 'registrant');

    expect($response)->toBeEmpty();

    // Step three: Upload the initial document for a user application
    $response = $sdk->policies->registerApplication($policy_id, $registration);

    expect($response)->toBeEmpty();

    // Step four: approve a document through the standard registry
    $response = $sdk->accounts->login('dovuauthority', 'secret');
    $registry_token = $response['data']['accessToken'];

    // Set the API token for the context of the standard reg
    $sdk->setApiToken($registry_token);

    $response = $sdk->policies->approveApplication($policy_id, $user_did);

    expect($response)->toBeEmpty();

    // Step five: Upload the ecological project for approval as a registrant
    $sdk->setApiToken($user_token);

    // Add sleepy time to give the guardian time to breathe... 😬
    sleep(10);

    $response = $sdk->policies->submitProject($policy_id, $ecological_project);

    expect($response)->toBeEmpty();


})->with('registration', 'ecological_project', 'agrecalc_mrv');
