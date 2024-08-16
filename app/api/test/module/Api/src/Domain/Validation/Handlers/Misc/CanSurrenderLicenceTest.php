<?php

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

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $isSurrenderable, $expected)
    {
        $licenceId = 1;
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn($licenceId);

        $this->setIsValid('canAccessLicence', [$licenceId], $canAccess);
        $this->setIsValid('isLicenceSurrenderable', [$licenceId], $isSurrenderable);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
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
