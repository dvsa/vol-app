<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessEbsrSubmissionWithId;

/**
 * @covers Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessEbsrSubmissionWithId
 */
class CanAccessEbsrSubmissionWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessEbsrSubmissionWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessEbsrSubmissionWithId();
        parent::setUp();
    }

    /**
     *
     * @param $canAccess
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $id = 111;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($id);

        $this->setIsValid('canAccessEbsrSubmission', [$id], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @return array
     */
    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
