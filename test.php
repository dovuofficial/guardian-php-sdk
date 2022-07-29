<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$sdk = new DovuGuardianAPI;

// ray($sdk->accounts->create('jon', 'secret'));

$response = $sdk->accounts->login('jon', 'secret');

ray($response);

// $sdk->setApiToken($response['data']['accessToken']);

// ray($sdk->client);




