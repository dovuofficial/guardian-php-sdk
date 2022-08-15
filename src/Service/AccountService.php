<?php

namespace Dovu\GuardianPhpSdk\Service;

class AccountService extends AbstractService
{
    public function login($username, $password)
    {
        return $this->client->post('accounts/login', [
                'username' => $username,
                'password' => $password,
        ]);
    }

    public function create($username, $password)
    {
        return $this->client->post('accounts', [
                'username' => $username,
                'password' => $password,
        ]);
    }

    public function role($policyId, $roleType)
    {
        return $this->client->post("policies/{$policyId}/role/{$roleType}");
    }
}
