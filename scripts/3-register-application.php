<?php

require('./vendor/autoload.php');

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

$policyId = '62fa59d271f8910e68012c6b';

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

$user = $sdk->accounts->login('jon', 'secret');

$sdk->setApiToken($user['data']['accessToken']);

$sdk->policies->registerApplication($policyId, $document);