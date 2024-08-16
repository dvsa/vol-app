<?php

/**
 * Can Access Xoc With Reference Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessXocWithReference;

/**
 * Can Access Xoc With Reference Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessXocWithReferenceTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessXocWithReference
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessXocWithReference();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('L111');

        $this->setIsValid('canAccessLicenceOperatingCentre', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidApplication($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('A111');

        $this->setIsValid('canAccessApplicationOperatingCentre', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testIsValidNeither()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('S111');

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
