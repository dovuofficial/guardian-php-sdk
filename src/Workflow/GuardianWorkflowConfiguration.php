<?php

namespace Dovu\GuardianPhpSdk\Workflow;

class GuardianWorkflowConfiguration
{
    public array $config = [];

    // This is the parsed and processed workflow from a template
    public array $workflow = [];

    private function __construct($name)
    {
        $this->config = include realpath('.') . "/config/$name.php";
    }

    public static function prepare($name): self
    {
        return (new self($name))->prepareConfigurationWorkflow();
    }

    public function getTemplate($item = "core.template"): string
    {
        $conf = (object) $this->config;
        $path = "config/templates/$conf->template/$item.edn";

        return shell_exec("bin/template-parser.sh $path");
    }

    public function getCoreTemplate(): array
    {
        return json_decode($this->getTemplate());
    }

    public function getElementTemplate($element): object
    {
        return json_decode($this->getTemplate($element));
    }

    public function timestamp(): string
    {
        return $this->config["import"]["timestamp"];
    }

    /**
     * TODO: additional work is required to ensure that a given configuration
     * is valid and has the minimum viable keys.
     *
     * This "might" be added to "Paladin" itself.
     *
     * @return self
     */
    public function prepareConfigurationWorkflow(): self
    {
        $core = $this->getCoreTemplate();

        $step_import = function ($step) {

            $role = strtoupper($step->role);
            $type = strtoupper($step->type);

            $step->role = constant("Dovu\GuardianPhpSdk\Constants\GuardianRole::$role");
            $step->type = constant("Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask::$type");

            if (isset($step->options)) {

                $options = [];

                foreach ($step->options as $option) {
                    $template = $this->getElementTemplate($option);

                    $template->status = constant($template->status)->value;
                    $template->option = constant($template->option)->value;

                    $option_key = explode('.', $option)[0];

                    $options[$option_key] = $template;
                }

                $step->options = $options;
            }

            return $step;
        };

        $this->workflow = array_map($step_import, $core);

        return $this;
    }
}
