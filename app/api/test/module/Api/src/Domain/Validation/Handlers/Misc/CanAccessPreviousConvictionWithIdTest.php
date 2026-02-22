<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessPreviousConvictionWithId;

/**
 * CanAccessPreviousConvictionWithIdTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessPreviousConvictionWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessPreviousConvictionWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessPreviousConvictionWithId();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);

        $this->setIsValid('canAccessPreviousConviction', [111], $canAccess);

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
