<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Available dot env items that can be consumed.
 *
 * @enum DotEnvItem
 */
enum Env: string
{
    case ALLOW_TESTS = 'ALLOW_SDK_TESTS';
    case HEDERA_NETWORK = 'HEDERA_NETWORK';
    case HEDERA_ACCOUNT_ID = 'HEDERA_ACCOUNT_ID';
    case HEDERA_PRIVATE_KEY = 'HEDERA_PRIVATE_KEY';
}
