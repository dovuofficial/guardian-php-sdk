<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$sdk = new DovuGuardianAPI;

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('1234567890');


$user = $sdk->accounts->login('jon', 'secret');

$userDid = $user['data']['did'];


$verifier = $sdk->accounts->login('verifier', 'secret');

$sdk->setApiToken($verifier['data']['accessToken']);


$sdk->mrv->approveMrvDocument($policyId, $userDid);