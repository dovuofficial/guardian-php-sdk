<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62f6226e8d44d2cbce22f03d';

$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('jon2', 'secret');

$registry = $sdk->accounts->login('dovuauthority', 'secret');

$sdk->setApiToken($registry['data']['accessToken']);

$sdk->accounts->role($policyId, 'registrant');

$did = $user['data']['did'];

$sdk->policies->approveApplication($policyId, $did);