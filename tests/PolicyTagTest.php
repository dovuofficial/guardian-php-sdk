<?php

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

it('SDK Can set a DOVU tag for a policy', function () {
    $sdk = new DovuGuardianAPI();

    $sdk->setGuardianBaseUrl('https://guardian-demo.dovu.dev/api/v1/');

    // Your standard registry pw
    $password = "123456";

    $login = $sdk->accounts->login('dovuauthority', $password);
    $token = $sdk->accounts->token($login->refreshToken);

    $sdk->setApiToken($token->accessToken);

    $policy_id = $sdk->config['local']['policy_id'];

    $tag = $sdk->tag->appendDovuPolicyTag($policy_id);

    $data = (object) $tag->data;

    expect($data->name)->toBeTruthy()
        ->and($data->description)->toBeTruthy()
        ->and($data->entity)->toBeTruthy()
        ->and($data->owner)->toBeTruthy()
        ->and($data->target)->toBeTruthy()
        ->and($tag->status_code)->toBe(201);
})->skip();
