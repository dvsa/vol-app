<?php

/**
 * Modify Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Vehicle\Application;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\Application\Modify;

/**
 * Modify Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ModifyTest extends AbstractHandlerTestCase
{
    /**
     * @var Modify
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Modify();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccessApplication, $canAccess1, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);
        $dto->shouldReceive('getApplication')->andReturn(123);

        $this->setIsValid('canAccessApplication', [123], $canAccessApplication);
        $this->setIsValid('canAccessLicenceVehicle', [111], $canAccess1);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true, true],
            [false, true, false],
            [true, false, false],
        ];
    }
}
