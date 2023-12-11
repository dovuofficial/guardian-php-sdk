<?php

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$sdk = new DovuGuardianAPI();

it('Check that the hmac amd api uri is set', function () use ($sdk) {
    $app = $sdk->config['app'];

    expect($app['base_url'])->toBe($app['base_url']);
    expect($app['base_url'])->not()->toBe("");
});

it('Check that a local policy is set', function () use ($sdk) {
    expect($sdk->config['local']['policy_id'])->not()->toBe("");
    expect($sdk->config['local']['hmac_secret'])->not()->toBe("");
});
