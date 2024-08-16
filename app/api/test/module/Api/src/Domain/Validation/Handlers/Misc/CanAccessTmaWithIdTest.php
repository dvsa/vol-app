<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessTmaWithId;

/**
 * CanAccessTmaWithIdTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTmaWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessTmaWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessTmaWithId();

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

        $this->setIsValid('canAccessTransportManagerApplication', [111], $canAccess);

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
