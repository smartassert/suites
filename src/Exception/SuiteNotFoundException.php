<?php

declare(strict_types=1);

namespace App\Exception;

class SuiteNotFoundException extends \Exception
{
    public const MESSAGE = 'Suite "%s" not found';

    public function __construct(private string $suiteId)
    {
        parent::__construct(sprintf(self::MESSAGE, $suiteId));
    }

    public function getSuiteId(): string
    {
        return $this->suiteId;
    }
}
