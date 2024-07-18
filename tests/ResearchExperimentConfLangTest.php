<?php

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Experimental\PaladinBlockElement;
use Dovu\GuardianPhpSdk\Experimental\PaladinConfiguration;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\GuardianWorkflowConfiguration;

describe('Experiment PHP Language for Paladin configuration', function () {

    it('You can describe paladin configuration in PHP for guardian and it should match the output for DSL', function () {

        $configuration = PaladinConfiguration::create(
            // Defines the first data requirement for a participant (registration)
            PaladinBlockElement::new()
                ->setRole(GuardianRole::PARTICIPANT)
                ->setTag("create_pp_profile")
                ->setWorkflowTask(WorkflowTask::DATA),
            // Defines the first data requirement for a VVB (registration)
            PaladinBlockElement::new()
                ->setRole(GuardianRole::VVB)
                ->setTag("create_new_vvb")
                ->setWorkflowTask(WorkflowTask::DATA),
            // Defines the project data requirement for a participants project (project submission)
            PaladinBlockElement::new()
                ->setRole(GuardianRole::PARTICIPANT)
                ->setTag("add_project_bnt")
                ->setWorkflowTask(WorkflowTask::DATA),
            // Defines the project data requirement for a participants monitoring report (project submission)
            PaladinBlockElement::new()
                ->setRole(GuardianRole::PARTICIPANT)
                ->setTag("add_report_bnt")
                ->setWorkflowTask(WorkflowTask::DATA)
        )->setTemplateName("ACM0001");

        ray($configuration->asWorkflow());

        $conf = GuardianWorkflowConfiguration::prepare('acm0001_workflow');

        ray($conf);

        expect($configuration->asWorkflow()->config["template"])->toBe($conf->config["template"]);
    });

});
