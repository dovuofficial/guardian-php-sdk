<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fa59d271f8910e68012c6b';

$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->accounts->role($policyId, 'registrant');