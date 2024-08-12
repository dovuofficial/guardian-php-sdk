<?php

namespace Dovu\GuardianPhpSdk\Domain;

/**
 * The purpose of this file, will break down later into separate files, Is to enable the ingestion Of any kind of guardian schema
 * When this works with paladin, this will unlock the ability for a system to create and manage a schema, with the portion of our workflows
 * elegantly, and in addition, it will provide the capability to validate input data against what the Guardian is expecting,
 * this intern using laravel Validation libraries should enable a cleaner interface between schema management and validation of input.
 */
// TODO: To be used for field validations, then mapped to laravel validation props (separate file).
enum PropertyFieldType: string
{
    case String = 'string';
    case Number = 'number';
    case Object = 'object';
}

class WriteablePropertyField
{
    public string $key;
    public string $title;
    public string $description;
    public PropertyFieldType $type;
    public object $comment;

    public function __construct(string $key, array $field)
    {
        $field = (object) $field;

        $this->key = $key;
        $this->title = $field->title;
        $this->description = $field->description;
        $this->type = PropertyFieldType::from($field->type);
        $this->comment = (object) $field->{'$comment'};
    }
}

class SchemaProperties
{
    public array $writable_property_fields = [];

    public array $required;

    public array $properties;

    public const SCHEMA_WHITELIST = ['title', 'type', 'description', 'enum', 'required'];

    private function __construct(
        public PolicySchemaDocument $document,
        public array $schema_document = []
    ) {
        $this->properties = $schema_document["properties"];
        $this->required = $schema_document["required"];
    }

    public static function inject(PolicySchemaDocument $document, array $properties): SchemaProperties
    {
        return new self($document, $properties);
    }

    private function writeableFields($properties): array
    {
        return array_filter(
            $properties,
            fn ($v) => ! $v['readOnly'],
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function processSchemaField(array $field)
    {
        if (array_key_exists('type', $field)) {
            return array_intersect_key($field, array_flip(self::SCHEMA_WHITELIST));
        }

        $def = $this->document->schema_defs[$field['$ref']];

        if ($def) {
            return SchemaProperties::inject($this->document, $def)
                ->processPropertyFields()
                ->export();
        }

        return [];
    }

    public function processPropertyFields(): self
    {
        $writable_fields = $this->writeableFields($this->properties);

        $property_spec = [];

        foreach ($writable_fields as $key => $field) {
            $property_spec[$key] = [
                'required' => in_array($key, $this->required),
                ...$this->processSchemaField($field),
            ];
        }

        $this->writable_property_fields = $property_spec;

        return $this;
    }

    public function export(): array
    {
        return [
            ...$this->processSchemaField($this->schema_document),
            ...$this->writable_property_fields,
        ];
    }
}

class PolicySchemaDocument
{
    public array $schema_defs;

    private SchemaProperties $properties;

    public function __construct(array $schema_document)
    {
        $this->schema_defs = $schema_document['$defs'];
        $this->properties = SchemaProperties::inject($this, $schema_document)
            ->processPropertyFields();
    }

    public function schemaValidationSpecification(): array
    {
        return $this->properties->export();
    }
}
