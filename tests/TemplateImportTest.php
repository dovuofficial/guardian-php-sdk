<?php


use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\GuardianWorkflowConfiguration;

describe('Workflow template import using Jet', function () {

    it('Should be able to import a template and make basic assertions', function () {

        $conf = GuardianWorkflowConfiguration::prepare('test_workflow');

        $template = $conf->workflow;

        // Can we expect enum types?
        $assert_elem_struct = function ($elem) {
            expect($elem->role)->toBeTruthy();
            expect($elem->key)->toBeTruthy();
            expect($elem->tag)->toBeTruthy();
            expect($elem->type)->toBeTruthy();

            if (isset($elem->filter)) {
                expect($elem->filter)->toBeTruthy();
                expect($elem->filter->tag)->toBeTruthy();
                expect($elem->filter->key)->toBeTruthy();
            }

            if (isset($elem->source_tag)) {
                expect($elem->source_tag)->toBeTruthy();
                expect($elem->require->status)->toBeTruthy();
            }

            if ($elem->type === WorkflowTask::DATA) {
                expect($elem->role)->toBe(GuardianRole::SUPPLIER);
            }

            if ($elem->type === WorkflowTask::APPROVAL) {
                expect($elem->role)->not()->toBe(GuardianRole::SUPPLIER);
                expect($elem->filter)->toBeTruthy();
                expect($elem->options)->toBeTruthy();
            }
        };

        array_map($assert_elem_struct, $template);
    });

})->with();
