<?php

namespace Dovu\GuardianPhpSdk\Domain;

use Dovu\GuardianPhpSdk\Support\PolicyWorkflow;

class PolicyConfiguration
{
    public const BLOCK_TYPES = [
        "policyRolesBlock",
        "requestVcDocumentBlock",
        "interfaceDocumentsSourceBlock",
        "documentsSourceAddon",
        "filtersAddon",
        "buttonBlock",
    ];

    public const BLOCK_IDS = ['id', 'tag', 'blockType', 'permissions', 'schema', 'events'];


    public object $policy;

    public function __construct(
        public PolicyWorkflow $workflow
    ) {
        $this->policy = $this->workflow->getPolicy();
    }

    public function roles(): array
    {
        return $this->policy->policyRoles;
    }

    public function findBlocksByTag($data, $tag, &$result = null): ?object
    {
        if ($data->tag == $tag) {
            $result = $data;

            return $result;
        }

        foreach ($data->children as $child) {
            $this->findBlocksByTag((object) $child, $tag, $result);
        }

        return $result;
    }

    public function generateBasicWorkflowSpecification(array $workflow)
    {
        return $this->generateWorkflowSpecification($workflow, false);
    }

    public function generateWorkflowSpecification(array $workflow, bool $with_schemas = true)
    {
        $specification = [];

        foreach ($workflow as $item) {

            $block = $this->findBlocksByTag($this->config(), $item->tag);
            $schema_spec = [];

            if ($with_schemas && isset($block->schema)) {
                $raw_schema = $this->workflow->getSchemaForKey($block->schema);
                $document = json_decode($raw_schema->document, true);

                $schema_spec = (new PolicySchemaDocument($document))->schemaValidationSpecification();
            }

            $specification[] = [
                ...(array) $item,
//                "block" => $block, // might be removed
                "schema_specification" => $schema_spec,
            ];
        }

        return $specification;
    }

    public function config(): object
    {
        return (object) $this->policy->config;
    }
}
