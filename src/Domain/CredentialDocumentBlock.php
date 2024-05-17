<?php

namespace Dovu\GuardianPhpSdk\Domain;

class CredentialDocumentBlock
{
    // Represents the raw data for fetching data for a block
    private array $block_data;

    // Credential subject that can be read inside the nested raw data
    private object $credential_subject;

    // Tag is assigned to a document on future submissions
    private ?string $tag = null;

    // Ref of previous object in a chain
    private ?object $ref = null;

    /**
     * @param object $block_data
     */
    public function __construct(object $block_data)
    {
        /**
         * This is the difference between a "requestVcDocumentBlock" and an "interface(filter)SourceBlock" should be simpler
         *
         * So the data from a block is a single "item" or first from the list.
         */
        $this->block_data = $block_data->data['0'] ?? $block_data->data;

        // TODO: Add safety checks for extracting object
        $this->credential_subject = (object) $this->block_data['document']['credentialSubject']['0'];
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

    public function getBlockData(): array
    {
        return $this->block_data;
    }

    /**
     * Provides a structure for document mutation, like approvals from a separate actor.
     *
     * @return array
     */
    public function forDocumentSubmission(): array
    {
        return [
            'document' => $this->block_data,
            'tag' => $this->tag,
        ];
    }

    /**
     * Provides a structure to chain in the current document as a reference to a future document within the workflow. This would cover all submissions of documents,
     * where two parts workflow need to be connected, Like creating a new site using the previous "project" as a reference.
     *
     * @param $document
     * @return array
     */
    public function chainDocumentAsReference($document): array
    {
        return [
            'document' => $document,
            'tag' => $this->tag,
            'ref' => $this->block_data,
        ];
    }

    public function updateStatus(string $status)
    {
        $this->block_data['option']['status'] = $status;
    }

    public function assignTag(string $tag)
    {
        $this->tag = $tag;
    }

    public function getStatus(): string
    {
        return $this->block_data['option']['status'];
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}