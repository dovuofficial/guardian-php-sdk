<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum GuardianApprovalOption: string
{
    case APPROVE = 'Option_0';
    case DENY = 'Option_1';
}
