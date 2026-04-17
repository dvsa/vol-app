<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Client\ClientOptions;

trait ClientOptionsTestTrait
{
    protected function getClientOptions(): ClientOptions
    {
        return new ClientOptions(
            2,
            'client_credentials',
            15.0,
            'api.cpms.domain',
            [
                'Accept' => 'application/json'
            ]
        );
    }
}
