<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum EntityStatus: string
{
    case WAITING = 'Waiting for approval';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case MINTING = 'Minting';
}
