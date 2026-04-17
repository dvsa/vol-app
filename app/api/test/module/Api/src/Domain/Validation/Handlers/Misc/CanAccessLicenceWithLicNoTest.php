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
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceWithLicNo;

/**
 * Can Access Licence With LicNo
 */
class CanAccessLicenceWithLicNoTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessLicenceWithLicNo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceWithLicNo();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getLicenceNumber')->andReturn('AB1234');

        $this->setIsValid('canAccessLicence', ['AB1234'], $canAccess);

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
