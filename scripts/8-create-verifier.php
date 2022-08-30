<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$sdk = new DovuGuardianAPI;

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->create('verifier', 'secret');

$user = $sdk->accounts->login('verifier', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->accounts->role($policyId, 'VERIFIER');