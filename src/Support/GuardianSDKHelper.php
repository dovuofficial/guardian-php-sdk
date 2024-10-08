<?php

namespace Dovu\GuardianPhpSdk\Support;

use Closure;
use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Constants\StateQuery;
use Dovu\GuardianPhpSdk\DovuGuardianAPI;

class GuardianSDKHelper
{
    public $sdk;

    private string $policyId;

    public static function actions(...$actions): Closure
    {
        return function () use ($actions) {
            $carry = null;
            foreach ($actions as $action) {
                $carry = $action($carry);
            }

            return $carry;
        };
    }

    public function __construct(DovuGuardianAPI $sdk, string $policyId = null)
    {
        $this->sdk = $sdk;
        $this->policyId = $policyId ?? $sdk->config['local']['policy_id'];
    }

    public function setApiKey($token): void
    {
        $this->sdk->setApiToken($token);
    }

    public function setRole(GuardianRole $role): void
    {
        $this->sdk->accounts->role($this->policyId, $role->value);
    }

    public function accessTokenForRegistry($username = 'dovuauthority', $password = '123456')
    {
        return $this->getAccessToken($username, $password);
    }

    public function authenticateAsRegistry($username = 'dovuauthority', $password = '123456'): void
    {
        $token = $this->accessTokenForRegistry($username, $password);
        $this->setApiKey($token);
    }

    public function authenticateAsActor($username, $password = '123456'): void
    {
        $this->authenticateAsRegistry($username, $password);
    }

    public function accessTokenForSupplier($username = 'supplier', $password = 'secret')
    {
        return $this->getAccessToken($username, $password);
    }

    public function accessTokenForVerifier($username = 'verifier', $password = 'secret')
    {
        return $this->getAccessToken($username, $password);
    }

    public function getAccessToken($username = '', $password = 'test')
    {
        $login = $this->sdk->accounts->login($username, $password);

        $refresh_token = $login->refreshToken;
        $token = $this->sdk->accounts->token($refresh_token);

        return $token->accessToken;
    }

    public function createNewUser($username = '', $password = 'test')
    {
        return $this->sdk->accounts->create($username, $password);
    }

    public function queryWaiting(StateQuery $query)
    {
        return $this->sdk->state->status(EntityStatus::WAITING)->query($this->policyId, $query);
    }

    public function queryApproved(StateQuery $query)
    {
        return $this->sdk->state->status(EntityStatus::APPROVED)->query($this->policyId, $query);
    }

    public function queryState(StateQuery $query)
    {
        return $this->sdk->state->query($this->policyId, $query);
    }

    /**
     * @throws \Exception
     */
    public function stateEntityListener(EntityStateWaitingQuery $state)
    {
        if (! $state->canAttemptQuery()) {
            throw new \Exception('Entity query attempts exhausted.');
        }

        $query = $this->sdk->state
            ->status($state->status)
            ->filters($state->getFilter())
            ->query($this->policyId, $state->query);

        if ($query->count) {
            return (object) $query->result[0];
        }

        ray("reattempt in $state->wait_seconds seconds");

        sleep($state->wait_seconds);

        $state->incrementAttempt();

        return $this->stateEntityListener($state);
    }

    public function createProject($document)
    {
        return $this->sdk->policies->createProject($this->policyId, $document);
    }

    /** to be executed by the standard registry */
    public function approveProject($entity_id)
    {
        return $this->sdk->policies->approveProject($this->policyId, $entity_id);
    }

    public function createSite($projectId, $document)
    {
        return $this->sdk->policies->createSite($this->policyId, $projectId, $document);
    }

    /** to be executed by the standard registry */
    public function approveSite($entity_id)
    {
        return $this->sdk->policies->approveSite($this->policyId, $entity_id);
    }

    public function createClaim($siteId, $document)
    {
        return $this->sdk->policies->createClaim($this->policyId, $siteId, $document);
    }

    /** to be executed by a verifier */
    public function approveClaim($entity_id)
    {
        return $this->sdk->policies->approveClaim($this->policyId, $entity_id);
    }
}
