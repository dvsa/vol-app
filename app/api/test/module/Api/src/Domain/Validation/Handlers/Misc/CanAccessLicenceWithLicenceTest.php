<?php

declare(strict_types=1);

/**
 * Can Access Licence With Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicence;

/**
 * Can Access Licence With Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceWithLicenceTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessLicenceWithLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceWithLicence();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getLicence')->andReturn(111);

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
