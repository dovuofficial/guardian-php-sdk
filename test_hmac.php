<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;
use Dovu\GuardianPhpSdk\Service\HmacService;
use Carbon\Carbon;

//"Wed, 03 Aug 2022 15:54:39 GMT"
// $hmacService = new HmacService('test');
// $hmac = $hmacService->build('post', '/test');


$hmacService = new HmacService(
    'post',
    'http://localhost:3001/api/accounts/login',
    ['username' => 'jon', 'password' => 'secret'],
    '1234567890'
);


ray($hmacService->get());