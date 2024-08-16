<?php

/**
 * Can Access Licence With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicNo;

/**
 * Can Access Licence With LicNo
 */
class CanAccessLicenceWithLicNoTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessLicenceWithLicNo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceWithLicNo();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getLicenceNumber')->andReturn('AB1234');

        $this->setIsValid('canAccessLicence', ['AB1234'], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
