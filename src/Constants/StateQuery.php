<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents the various states a policy can have in the workflow process.
 *
 * This enum is used to query specific stages in the lifecycle of a policy,
 * allowing for targeted actions and data retrieval based on the current state.
 *
 * @enum StateQuery
 */
enum StateQuery: string
{
    case PROJECTS = 'projects';
    case CREATE_SITE = 'create-site';
    case APPROVE_SITE = 'approve-site';
    case CREATE_CLAIM = 'create-claim';
    case APPROVE_CLAIM = 'approve-claim';
}
