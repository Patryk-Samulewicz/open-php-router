<?php

declare(strict_types=1);

namespace OpenPhpRouter\Enum;

enum RoleType: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case TOOL = 'tool';
} 