<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->create('jon', 'secret');

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->accounts->role($policyId, 'registrant');