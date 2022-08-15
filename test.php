<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62f6226e8d44d2cbce22f03d';

$document = '{
        "field0": "Blanditiis consequuntur repellat voluptatem quod et aperiam voluptas.",
        "field1": "A at mollitia corporis molestiae ut debitis.",
        "field2": 57779,
        "field3": "Illum commodi quidem dolorem voluptatibus.",
        "field4": "Porro qui error earum quia iure praesentium molestiae.",
        "field5": "Aut necessitatibus voluptatem quae nemo reiciendis officia et aperiam quia.",
        "field6": "Quia maiores vel et reprehenderit eius fugiat quae nihil.",
        "field7": "Aliquid et sint sint assumenda nostrum eum.",
        "field8": "Quia explicabo dolorum minima perspiciatis suscipit odit explicabo aut amet."
}';


$sdk = new DovuGuardianAPI;

$sdk->setHmacSecret('1234567890');

// $response = $sdk->accounts->create('jon2', 'secret');

$user = $sdk->accounts->login('jon2', 'secret');

// $registry = $sdk->accounts->login('dovuauthority', 'secret');

// $sdk->setApiToken($registry['data']['accessToken']);

$sdk->setApiToken($user['data']['accessToken']);

$sdk->accounts->role($policyId, 'registrant');

// $sdk->policies->registerApplication($policyId, $document);

// $did = $user['data']['did'];

// $sdk->policies->approveApplication($policyId, $did);








