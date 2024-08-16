<?php

/**
 * Can Access Organisation Person With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationPersonWithId;

/**
 * Can Access Organisation Person With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationPersonWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessOrganisationPersonWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessOrganisationPersonWithId();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);

        $this->setIsValid('canAccessOrganisationPerson', [111], $canAccess);

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
