<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithIrhpApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits\CanAccessIrhpApplicationWithIrhpApplication
 */
class CanAccessIrhpApplicationWithIrhpApplicationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessIrhpApplicationWithIrhpApplication
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessIrhpApplicationWithIrhpApplication();

        parent::setUp();
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIrhpApplication')->andReturn($id);

        $this->setIsValid('canAccessIrhpApplicationWithId', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function dpTestIsValid()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
