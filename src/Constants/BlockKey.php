<?php

namespace Dovu\GuardianPhpSdk\Constants;

enum BlockKey: string
{
    // Format "#a753238f-d4f5-4849-9249-fa93a4cc6365"
    case IRI = 'iri';
    // Format "a753238f-d4f5-4849-9249-fa93a4cc6365"
    case UUID = 'uuid';
    case NAME = 'name';
}
