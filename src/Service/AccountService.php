<?php

namespace Dovu\GuardianPhpSdk\Service;

class AccountService extends AbstractService
{
    public function login($username, $password): object
    {
        return (object) $this->httpClient->post('accounts/login', [
                'username' => $username,
                'password' => $password,
        ], true);
    }

    public function create($username, $password)
    {
        return $this->httpClient->post('accounts', [
                'username' => $username,
                'password' => $password,
        ], true);
    }

    public function token($refresh_token): object
    {
        return (object) $this->httpClient->post('accounts/access-token', [
            'refreshToken' => $refresh_token,
        ], true);
    }

    public function role($policyId, $roleType)
    {
        return $this->httpClient->post("policies/{$policyId}/role/{$roleType}");
    }
}
