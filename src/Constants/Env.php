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

    /**
     * Having a policy id allow for a policy to not be imported on-the-fly, so that
     * local testing can save time.
     *
     * Note: should be empty for CI testing usage.
     */
    case POLICY_ID = 'POLICY_ID';

    /**
     * These standard registry details allow us to work with a local standard
     * registry user without needing to generate new keys for a new user, the
     * impact is that the limited resource of testnet HBARs and facet usage
     * can be minimised.
     */
    case STANDARD_REGISTRY_USERNAME = 'STANDARD_REGISTRY_USERNAME';
    case STANDARD_REGISTRY_PASSWORD = 'STANDARD_REGISTRY_PASSWORD';
}
