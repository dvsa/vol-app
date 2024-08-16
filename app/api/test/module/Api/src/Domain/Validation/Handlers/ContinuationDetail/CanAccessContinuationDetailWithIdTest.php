<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\ContinuationDetail;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\ContinuationDetail\CanAccessContinuationDetailWithId;

class CanAccessContinuationDetailWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessContinuationDetailWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessContinuationDetailWithId();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     *
     * @param bool $canAccess can access
     * @param bool $expected  expected
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessContinuationDetail', [76], $canAccess);

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
