<?php

declare(strict_types=1);

/**
 * Delete Application Links Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OperatingCentre;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\OperatingCentre\DeleteApplicationLinks as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Application Links Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteApplicationLinksTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            OperatingCentre::class => [
                1 => m::mock(OperatingCentre::class),
            ],
            ApplicationOperatingCentre::class => [
                1 => m::mock(ApplicationOperatingCentre::class),
                2 => m::mock(ApplicationOperatingCentre::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $oc = $this->mapReference(OperatingCentre::class, 1);
        $aoc1 = $this->mapReference(ApplicationOperatingCentre::class, 1);
        $aoc2 = $this->mapReference(ApplicationOperatingCentre::class, 2);

        $data = ['operatingCentre' => $oc];

        $command = Cmd::create($data);

        $aoc1
            ->shouldReceive('getApplication->isUnderConsideration')
            ->andReturn(true);

        $aoc2
            ->shouldReceive('getApplication->isUnderConsideration')
            ->andReturn(false);

        $oc->shouldReceive('getApplications')
            ->andReturn([$aoc1, $aoc2]);

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('delete')
            ->with($aoc1)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Delinked Operating Centre from 1 other Application(s)',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
