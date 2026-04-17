<?php

declare(strict_types=1);

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
     *
     * @param bool $canAccess can access
     * @param bool $expected  expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(76);

        $this->setIsValid('canAccessContinuationDetail', [76], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
