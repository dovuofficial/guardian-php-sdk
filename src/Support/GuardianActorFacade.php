<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\HederaAccount;
use Dovu\GuardianPhpSdk\Domain\RegistryAccount;

class GuardianActorFacade
{
    public PolicyContext $context;

    private function __construct(PolicyContext $context)
    {
        $this->context = $context;
    }

    public static function context(PolicyContext $context): self
    {
        return new self($context);
    }

    public function newRegistryAccount(string $username, string $password): object
    {
        return $this->context->accounts->register($username, $password, GuardianRole::REGISTRY);
    }

    public function generateDemoKey(): HederaAccount
    {
        $task = $this->context->accounts->generateDemoKey();
        $response = $this->context->accounts->syncRunnerTask($task);

        $result = (object) $response->result;

        return new HederaAccount($result->id, $result->key);
    }

    public function addHederaAccountToActor(string $username, HederaAccount $account): object
    {
        $account_key_update = RegistryAccount::with($account)->keyRegistrationFormat();

        $task = $this->context->accounts->update($username, $account_key_update);

        return $this->context->accounts->syncRunnerTask($task);
    }
}
