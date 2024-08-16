<?php

/**
 * Can Access Organisation With Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationWithOrganisation;

/**
 * Can Access Organisation With Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationWithOrganisationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessOrganisationWithOrganisation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessOrganisationWithOrganisation();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getOrganisation')->andReturn(111);

        $this->setIsValid('canAccessOrganisation', [111], $canAccess);

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
