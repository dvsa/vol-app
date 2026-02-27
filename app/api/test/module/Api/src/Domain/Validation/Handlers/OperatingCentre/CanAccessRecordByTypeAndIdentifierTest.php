<?php

declare(strict_types=1);

/**
 * Can Access Record By Type And Identifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre\CanAccessRecordByTypeAndIdentifier;

/**
 * Can Access Record By Type And Identifier Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessRecordByTypeAndIdentifierTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessRecordByTypeAndIdentifier
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessRecordByTypeAndIdentifier();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getType')->andReturn('licence');
        $dto->shouldReceive('getIdentifier')->andReturn(111);

        $this->setIsValid('canAccessLicence', [111], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidApplication(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getType')->andReturn('application');
        $dto->shouldReceive('getIdentifier')->andReturn(111);

        $this->setIsValid('canAccessApplication', [111], $canAccess);

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
