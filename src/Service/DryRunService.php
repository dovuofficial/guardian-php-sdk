<?php

namespace Dovu\GuardianPhpSdk\Service;

class DryRunService extends AbstractService
{
    public function restart($id): object
    {
        return (object) $this->httpClient->post("policies/{$id}/dry-run/restart")->data();
    }

    public function start($id): object
    {
        return (object) $this->httpClient->put("policies/{$id}/dry-run")->data();
    }

    public function stop($id): object
    {
        return (object) $this->httpClient->put("policies/{$id}/draft")->data();
    }

    public function login($id, $did): object
    {
        return (object) $this->httpClient->post("policies/{$id}/dry-run/login", [
            'did' => $did,
        ], true)->data();
    }

    public function createUser($id): array
    {
        return $this->httpClient->post("policies/{$id}/dry-run/user")->data();
    }

    public function users($id): array
    {
        return $this->httpClient->get("policies/{$id}/dry-run/users")->data();
    }
}
