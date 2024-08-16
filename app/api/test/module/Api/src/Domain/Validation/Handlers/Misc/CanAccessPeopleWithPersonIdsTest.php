<?php

/**
 * Can Access People With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessPeopleWithPersonIds;

/**
 * Can Access People With Person Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessPeopleWithPersonIdsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessPeopleWithPersonIds
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessPeopleWithPersonIds();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess1, $canAccess2, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getPersonIds')->andReturn([111, 222]);

        $this->setIsValid('canAccessPerson', [111], $canAccess1);
        $this->setIsValid('canAccessPerson', [222], $canAccess2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [
                true,
                true,
                true
            ],
            [
                false,
                false,
                false
            ],
            [
                true,
                false,
                false
            ],
            [
                false,
                true,
                false
            ]
        ];
    }
}
