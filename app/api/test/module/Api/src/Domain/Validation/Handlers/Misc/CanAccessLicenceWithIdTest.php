<?php

declare(strict_types=1);

/**
 * Can Access Licence With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithId;

/**
 * Can Access Licence With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessLicenceWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceWithId();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);

        $this->setIsValid('canAccessLicence', [111], $canAccess);

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
