<?php

declare(strict_types=1);

/**
 * Can Access Licence With Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanSurrenderLicence;

class CanSurrenderLicenceTest extends AbstractHandlerTestCase
{
    /**
     * @var CanSurrenderLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanSurrenderLicence();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $isSurrenderable, mixed $expected): void
    {
        $licenceId = 1;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($licenceId);

        $this->setIsValid('canAccessLicence', [$licenceId], $canAccess);
        $this->setIsValid('isLicenceSurrenderable', [$licenceId], $isSurrenderable);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            'case_01' => [
                'canAccess' => true,
                'isSurrenderable' => true,
                'expected' => true
            ],
            'case_02' => [
                'canAccess' => false,
                'isSurrenderable' => true,
                'expected' => false
            ],
            'case_03' => [
                'canAccess' => true,
                'isSurrenderable' => false,
                'expected' => false
            ],
            'case_04' => [
                'canAccess' => false,
                'isSurrenderable' => false,
                'expected' => false
            ]
        ];
    }
}
