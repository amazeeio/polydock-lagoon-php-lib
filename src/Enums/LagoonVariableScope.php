<?php

namespace FreedomtechHosting\FtLagoonPhp\Enums;

enum LagoonVariableScope: string
{
    case GLOBAL = 'GLOBAL';
    case RUNTIME = 'RUNTIME';
    case BUILD = 'BUILD';
    case CONTAINER_REGISTRY = 'CONTAINER_REGISTRY';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
