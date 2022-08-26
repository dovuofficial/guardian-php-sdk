<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

$authority = $sdk->accounts->login('dovuauthority', 'secret');

$sdk->setApiToken($authority['data']['accessToken']);

$trustchain = $sdk->policies->trustChain($policyId);

ray($trustchain);