<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanEditApplicationWithId;

/**
 * Can Edit Application With Id Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditApplicationWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessApplicationWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanEditApplicationWithId();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canEdit, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);

        $this->setIsValid('canEditApplication', [111], $canEdit);

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
