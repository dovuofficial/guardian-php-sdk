<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum GuardianRole: string
{
    case SUPPLIER = 'SUPPLIER';
    case VERIFIER = 'VERIFIER';
}
