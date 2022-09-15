<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

trait UnauthorizedUserDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'empty token' => [
                'token' => '',
            ],
            'non-empty invalid token' => [
                'token' => 'invalid api token value',
            ],
        ];
    }
}
