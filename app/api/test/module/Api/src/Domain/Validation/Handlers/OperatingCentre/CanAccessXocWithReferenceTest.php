<?php

declare(strict_types=1);

/**
 * Can Access Xoc With Reference Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessXocWithReference;

/**
 * Can Access Xoc With Reference Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessXocWithReferenceTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessXocWithReference
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessXocWithReference();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('L111');

        $this->setIsValid('canAccessLicenceOperatingCentre', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidApplication(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('A111');

        $this->setIsValid('canAccessApplicationOperatingCentre', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testIsValidNeither(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn('S111');

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
