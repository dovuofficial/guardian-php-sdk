<?php

namespace Dovu\GuardianPhpSdk\Domain;

class Block
{
    // Represents the raw data for fetching data for a block
    private object $block_data;

    // Credential subject that can be read inside the nested raw data
    private object $credential_subject;

    // The current status of the block that we are referencing
    private string $status;

    /**
     * @param object $block_data
     */
    public function __construct(object $block_data)
    {
        $this->block_data = $block_data;

        // TODO: Add safety checks for extracting object
        $this->credential_subject = (object) $block_data->data['0']['document']['credentialSubject']['0'];

        $this->status = $block_data->data['0']['option']['status'];
    }

    // Magic method usage to hoist up credential subject field, might revisit later
    public function __get($name)
    {
        return $this->getCredentialField($name);
    }

    public function getCredentialSubject(): object
    {
        return $this->credential_subject;
    }

    public function getCredentialField(string $key)
    {
        return $this->credential_subject->{$key} ?? null;
    }

    public function getBlockData(): object
    {
        return $this->block_data;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
