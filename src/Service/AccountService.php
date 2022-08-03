<?php

namespace Dovu\GuardianPhpSdk\Service;

class AccountService extends AbstractService
{
    public function login($username, $password)
    {
        return $this->client->post('accounts/login', [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);
    }

    public function create($username, $password)
    {
        return $this->client->post('accounts', [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);
    }

    public function role($policyId)
    {
        return $this->client->post("policies/{$policyId}/role");
    }
}
