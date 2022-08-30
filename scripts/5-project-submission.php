<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fcc7e54d3979bff880545f';

$document = '{
        "field0": "uuid",
        "field1": "Illum commodi quidem dolorem voluptatibus.",
        "field2": "A at mollitia corporis molestiae ut debitis.",
        "field3": "owner",
        "field4": {
            "field0": "dovu.market",
            "field1": "England",
            "field2": "Micro"
        },
        "field5": {
            "field0": "uuid",
            "field1": "GeoJSON Location",
            "field2": "Removal",
            "field3": "N/A",
            "field4": "N/A",
            "field5": "N/A",
            "field6": 1,
            "field7": "N/A",
            "field8": "Developer of project",
            "field9": "Sponsor (optional)",
            "field10": "Claim Tokens (number)"
        }
    }';


$sdk = new DovuGuardianAPI;

$sdk->setGuardianBaseUrl('http://localhost:3001/api/');

$sdk->setHmacSecret('1234567890');

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->policies->submitProject($policyId, $document);







