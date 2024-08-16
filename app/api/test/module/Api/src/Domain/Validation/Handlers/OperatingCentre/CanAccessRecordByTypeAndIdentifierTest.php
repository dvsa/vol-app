<?php

/**
 * Can Access Record By Type And Identifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessRecordByTypeAndIdentifier;

/**
 * Can Access Record By Type And Identifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessRecordByTypeAndIdentifierTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessRecordByTypeAndIdentifier
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessRecordByTypeAndIdentifier();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getType')->andReturn('licence');
        $dto->shouldReceive('getIdentifier')->andReturn(111);

        $this->setIsValid('canAccessLicence', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidApplication($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getType')->andReturn('application');
        $dto->shouldReceive('getIdentifier')->andReturn(111);

        $this->setIsValid('canAccessApplication', [111], $canAccess);

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
