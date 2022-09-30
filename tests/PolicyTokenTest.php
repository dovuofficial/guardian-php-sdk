<?php

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

it('SDK can get a token for a policy', function () {
    $sdk = new DovuGuardianAPI();

    $sdk->setGuardianBaseUrl('http://localhost:3001/api/');

    $sdk->setHmacSecret($sdk->config['local']['hmac_secret']);

    $registry = $sdk->accounts->login('dovuauthority', 'secret');

    $sdk->setApiToken($registry['data']['accessToken']);

    $policy_id = $sdk->config['local']['policy_id'];

    $token = $sdk->policies->token($policy_id);

    expect($token["data"]["policy_token_id"])->toBeTruthy();
});
