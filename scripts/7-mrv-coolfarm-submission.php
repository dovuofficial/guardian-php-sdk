<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$document = '{
    "field0": "Test MRV Field 1",
    "field1": 100,
    "field2": "Test MRV Field 3",
    "field3": 200,
    "field4": 300
}';


$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->mrv->submitCoolFarmToolDocument($policyId, $document);







