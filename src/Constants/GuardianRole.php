<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum GuardianRole: string
{
    case SUPPLIER = 'Supplier';
    case VERIFIER = 'Verifier';
    case USER = 'USER';
    case REGISTRY = 'STANDARD_REGISTRY';
}
