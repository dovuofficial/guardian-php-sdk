<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\HederaAccount;
use Dovu\GuardianPhpSdk\Domain\RegistryAccount;
use Dovu\GuardianPhpSdk\Domain\UserAccount;

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

    public function newUserAccount(string $username, string $password): object
    {
        return $this->context->accounts->register($username, $password, GuardianRole::USER);
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

    public function assignAccountToRegistry(string $username, string $registry_did): object
    {
        $account = $this->generateDemoKey();

        // ISSUE: hmm? (Generation of keys without wait is broken.)
        sleep(5);

        $account_key_update = UserAccount::with($account, $registry_did)->keyRegistrationFormat();

        $task = $this->context->accounts->update($username, $account_key_update);

        return $this->context->accounts->syncRunnerTask($task);
    }
}
