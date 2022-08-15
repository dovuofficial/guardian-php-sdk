<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62f6226e8d44d2cbce22f03d';

$sdk = new DovuGuardianAPI;

$user = $sdk->accounts->create('jon2', 'secret');