<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOtherLicenceWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * CanAccessOtherLicenceWithIdTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessOtherLicenceWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessOtherLicenceWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessOtherLicenceWithId();

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

        $this->setIsValid('canAccessOtherLicence', [111], $canAccess);

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
