<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\TaskInstance;

class AccountService extends AbstractService
{
    public function login($username, $password): object
    {
        return (object) $this->httpClient->post('accounts/login', [
                'username' => $username,
                'password' => $password,
        ], true)->data();
    }

    public function register($username, $password, GuardianRole $role): object
    {
        return (object) $this->httpClient->post('accounts/register', [
                'username' => $username,
                'password' => $password,
                'password_confirmation' => $password,
                'role' => $role->value,
        ], true)->data();
    }

    /**
     * This is used primarily for the initial key attachment to an actor
     */
    public function update(string $username, array $data): TaskInstance
    {
        $response = (object) $this->httpClient->put("profiles/push/$username", $data, true);

        return TaskInstance::from((object) $response->data());
    }

    /**
     * This is used primarily for the initial key attachment to an actor
     */
    public function profile(string $username): object
    {
        return (object) $this->httpClient->get("profiles/$username")->data();
    }

    public function session(): object
    {
        return (object) $this->httpClient->get('accounts/session')->data();
    }

    /**
     * This is to only be used in testnet and demo purposes
     */
    public function generateDemoKey(): TaskInstance
    {
        $response = (object) $this->httpClient->get('demo/push/random-key');

        return TaskInstance::from((object) $response->data());
    }

    public function create($username, $password)
    {
        return $this->httpClient->post('accounts', [
                'username' => $username,
                'password' => $password,
        ], true)->data();
    }

    public function token($refresh_token): object
    {
        return (object) $this->httpClient->post('accounts/access-token', [
            'refreshToken' => $refresh_token,
        ], true)->data();
    }

    public function role($policyId, $roleType)
    {
        return $this->httpClient->post("policies/{$policyId}/role/{$roleType}")->data();
    }
}
