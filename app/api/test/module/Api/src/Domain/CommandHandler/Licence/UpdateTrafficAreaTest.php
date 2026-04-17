<?php

declare(strict_types=1);

/**
 * UpdateTrafficAreaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateTrafficArea as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

/**
 * UpdateTrafficAreaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTrafficAreaTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficArea::class => [
                'Z' => m::mock(TrafficArea::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 52,
            'trafficArea' => 'Z',
            'version' => 4324,
        ];
        $command = Command::create($data);

        $licence = new LicenceEntity(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            false
        );
        $application->setId(34);
        $licence->addApplications($application);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 4324)->once()->andReturn($licence);

        $this->repoMap['Licence']->shouldReceive('save')->once()->andReturnUsing(
            function (LicenceEntity $saveLicence) {
                $this->assertSame($this->references[TrafficArea::class]['Z'], $saveLicence->getTrafficArea());
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber::class,
            ['id' => 34],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['Licence Traffic Area updated'], $response->getMessages());
    }
}
