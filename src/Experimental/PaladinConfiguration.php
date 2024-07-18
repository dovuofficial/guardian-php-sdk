<?php

namespace Dovu\GuardianPhpSdk\Experimental;

class PaladinConfiguration
{
    public array $blocks;

    public string $template;

    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    public static function create(...$elements)
    {
        $asObjects = array_map(fn ($e) => $e->toObject(), $elements);

        return new self($asObjects);
    }

    public function setTemplateName(string $template): PaladinConfiguration
    {
        $this->template = $template;

        return $this;
    }

    public function asWorkflow(): object
    {
        return (object) [
            "config" => [
                "template" => $this->template,
            ],
            "workflow" => $this->blocks,
        ];
    }
}
