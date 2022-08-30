<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$document = '{
    "field0": 100,
    "field1": 200,
    "field2": 300,
    "field3": 400,
    "field4": 500,
    "field5": 600,
    "field6": 700,
    "field7": 800,
    "field8": 900,
    "field9": 1000,
    "field10": 1100,
    "field11": 1200,
    "field12": 1300,
    "field13": 1400,
    "field14": 1500
}';


$sdk = new DovuGuardianAPI;

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->mrv->submitAgrecalcDocument($policyId, $document);







