<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessTmEmploymentWithId;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * CanAccessTmEmploymentWithId test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessTmEmploymenWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessTmEmploymentWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTmEmploymentWithId();

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

        $this->setIsValid('canAccessTmEmployment', [111], $canAccess);

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
