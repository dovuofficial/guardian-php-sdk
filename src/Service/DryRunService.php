<?php

namespace Dovu\GuardianPhpSdk\Service;

class DryRunService extends AbstractService
{
    public function restart($id): object
    {
        return (object) $this->httpClient->post("policies/{$id}/dry-run/restart");
    }

    public function start($id): object
    {
        return (object) $this->httpClient->put("policies/{$id}/dry-run");
    }

    public function stop($id): object
    {
        return (object) $this->httpClient->put("policies/{$id}/draft");
    }

    public function login($id, $did): object
    {
        return (object) $this->httpClient->post("policies/{$id}/dry-run/login", [
            'did' => $did,
        ], true);
    }

    public function createUser($id): array
    {
        return $this->httpClient->post("policies/{$id}/dry-run/user");
    }

    public function users($id): array
    {
        return $this->httpClient->get("policies/{$id}/dry-run/users");
    }
}
