<?php

declare(strict_types=1);

/**
 * Update Safety Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateSafety;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSafety as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Update Safety Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateSafetyTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateSafety();
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::TACH_EXT
        ];

        $this->references = [];

        parent::initReferences();
    }

    public static function dpHandleCommand(): array
    {
        return [
            [
                'canHaveTrailer' => true,
                'totAuthTrailers' => 12,
                'expectedSafetyInsTrailers' => 3,
            ],
            [
                'canHaveTrailer' => true,
                'totAuthTrailers' => 0,
                'expectedSafetyInsTrailers' => 0,
            ],
            [
                'canHaveTrailer' => false,
                'totAuthTrailers' => 12,
                'expectedSafetyInsTrailers' => null,
            ],
            [
                'canHaveTrailer' => false,
                'totAuthTrailers' => 0,
                'expectedSafetyInsTrailers' => null,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $canHaveTrailer, mixed $totAuthTrailers, mixed $expectedSafetyInsTrailers): void
    {
        $data = [
            'id' => 222,
            'version' => 1,
            'safetyInsVehicles' => 2,
            'safetyInsTrailers' => 3,
            'safetyInsVaries' => 'Y',
            'tachographIns' => LicenceEntity::TACH_EXT,
            'tachographInsName' => 'Some name'
        ];

        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setTotAuthTrailers($totAuthTrailers);
        $licence->shouldReceive('canHaveTrailer')
            ->withNoArgs()
            ->andReturn($canHaveTrailer);
        $licence->shouldReceive('updateSafetyDetails')
            ->withArgs(
                function (
                    $safetyInsVehicles,
                    $safetyInsTrailers,
                    $tachographIns,
                    $tachographInsName,
                    $safetyInsVaries
                ) use ($expectedSafetyInsTrailers) {
                    $this->assertSame(2, $safetyInsVehicles);
                    $this->assertSame($expectedSafetyInsTrailers, $safetyInsTrailers);
                    $this->assertSame($this->refData[LicenceEntity::TACH_EXT], $tachographIns);
                    $this->assertSame('Some name', $tachographInsName);
                    $this->assertSame('Y', $safetyInsVaries);
                    return true;
                }
            )
            ->once();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->once()
            ->andReturn($licence)
            ->shouldReceive('save')
            ->once()
            ->with($licence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Licence updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
