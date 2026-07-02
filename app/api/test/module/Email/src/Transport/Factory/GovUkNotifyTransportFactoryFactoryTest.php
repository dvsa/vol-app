<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Transport\Factory;

use Dvsa\Olcs\Email\Transport\Factory\GovUkNotifyTransportFactoryFactory;
use PHPUnit\Framework\TestCase;

class GovUkNotifyTransportFactoryFactoryTest extends TestCase
{
    public function testResolveGuzzleOptionsUsesConfiguredProxy(): void
    {
        $this->assertSame(
            ['proxy' => 'http://proxy.dev.olcs.dev-dvsacloud.uk:3128'],
            GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions('http://proxy.dev.olcs.dev-dvsacloud.uk:3128'),
        );
    }

    public function testResolveGuzzleOptionsIgnoresUnresolvedPlaceholder(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions('http://%shd_proxy%'));
    }

    public function testResolveGuzzleOptionsIgnoresEmptyString(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions(''));
    }

    public function testResolveGuzzleOptionsIgnoresNull(): void
    {
        $this->assertSame([], GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions(null));
    }
}
