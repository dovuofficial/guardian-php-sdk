<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum GuardianRole: string
{
    case REGISTRY = 'STANDARD_REGISTRY';
    case SUPPLIER = 'Supplier';
    case USER = 'USER';
    case VERIFIER = 'Verifier';
}
