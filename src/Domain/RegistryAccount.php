<?php

namespace Dovu\GuardianPhpSdk\Domain;

class RegistryAccount
{
    // Metadata fields related to registry
    private string $geography = "UK";
    private string $law = "UK";
    private string $tags = "DOVU";

    private HederaAccount $hedera_account;

    /**
     * @param HederaAccount $hedera_account
     */
    public function __construct(HederaAccount $hedera_account)
    {
        $this->hedera_account = $hedera_account;
    }

    public static function with(HederaAccount $account)
    {
        return new self($account);
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
            "didDocument" => null,
            "didKeys" => null,
            "fireblocksConfig" => [
                "fireBlocksApiKey" => "",
                "fireBlocksAssetId" => "",
                "fireBlocksPrivateiKey" => "",
                "fireBlocksVaultId" => "",
            ],
            "hederaAccountId" => $this->hedera_account->account_id,
            "hederaAccountKey" => $this->hedera_account->private_key,
            "useFireblocksSigning" => false,
            "vcDocument" => [
                "geography" => $this->geography,
                "law" => $this->law,
                "tags" => $this->tags,
            ],
        ];
    }

    public function setGeography(string $geography): self
    {
        $this->geography = $geography;

        return $this;
    }

    public function setLaw(string $law): self
    {
        $this->law = $law;

        return $this;
    }

    public function setTags(string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
