<?php

declare(strict_types=1);

namespace App\Controller;

class SuiteRoutes
{
    public const ROUTE_SUITE_ID_ATTRIBUTE = 'suiteId';
    public const ROUTE_SUITE_ID_PATTERN = '{' . self::ROUTE_SUITE_ID_ATTRIBUTE . '<[A-Z90-9]{26}>}';
    public const ROUTE_SUITE = '/' . self::ROUTE_SUITE_ID_PATTERN;
}
