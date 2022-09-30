<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '632b33532e11b6094416fc14';

$sdk = new DovuGuardianAPI;

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('dovuauthority', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$token = $sdk->policies->token($policyId);

dd($token);









