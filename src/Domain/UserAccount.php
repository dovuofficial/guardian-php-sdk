<?php

namespace Dovu\GuardianPhpSdk\Domain;

class UserAccount
{
    /**
     * @param HederaAccount $hedera_account
     * @param string $standard_registry_did
     */
    public function __construct(public HederaAccount $hedera_account, public string $standard_registry_did)
    {
    }

    public static function with(HederaAccount $account, string $standard_registry_did)
    {
        return new self($account, $standard_registry_did);
    }

    /**
     * This is simply focusing on the initial registration of an account where
     * keys have been generated outside of this class.
     *
     * @return null[]
     */
    public function keyRegistrationFormat(): array
    {
        return [
            "fireblocksConfig" => [
                "fireBlocksApiKey" => "",
                "fireBlocksAssetId" => "",
                "fireBlocksPrivateiKey" => "",
                "fireBlocksVaultId" => "",
            ],
            "hederaAccountId" => $this->hedera_account->account_id,
            "hederaAccountKey" => $this->hedera_account->private_key,
            "parent" => $this->standard_registry_did,
            "useFireblocksSigning" => false,
        ];

    }
}
