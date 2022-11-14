<?php

namespace Dovu\GuardianPhpSdk\Service;

class AccountService extends AbstractService
{
    public function login($username, $password)
    {
        return $this->httpClient->post('accounts/login', [
                'username' => $username,
                'password' => $password,
        ]);
    }

    public function create($username, $password)
    {
        return $this->httpClient->post('accounts', [
                'username' => $username,
                'password' => $password,
        ]);
    }

    public function role($policyId, $roleType)
    {
        return $this->httpClient->post("policies/{$policyId}/role/{$roleType}");
    }
}
