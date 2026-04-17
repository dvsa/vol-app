<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Helper;

use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Dvsa\OlcsTest\Api\Service\Helper\Stub\AddressFormatterAwareTraitStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait
 */
class AddressFormatterAwareTraitTest extends MockeryTestCase
{
    public function testGetSet(): void
    {
        /** @var FormatAddress $mockFormatter */
        $mockFormatter = m::mock(FormatAddress::class);

        $sut = new AddressFormatterAwareTraitStub();
        $sut->setAddressFormatter($mockFormatter);

        static::assertSame($mockFormatter, $sut->getAddressFormatter());
    }
}
