<?php

namespace Dovu\GuardianPhpSdk\Constants;

/**
 * Represents a status that an entity could be for query
 *
 * @enum EntityStatus
 */
enum GuardianRole: string
{
    // Default
    case SUPPLIER = 'Supplier';
    case VERIFIER = 'Verifier';
    case USER = 'USER';
    case REGISTRY = 'STANDARD_REGISTRY';
    case OWNER = 'OWNER';
    case NONE = 'NO_ROLE';

    // ACM0001 Methodology
    case PARTICIPANT = 'Project Participant';
    case VVB = 'VVB';


}
