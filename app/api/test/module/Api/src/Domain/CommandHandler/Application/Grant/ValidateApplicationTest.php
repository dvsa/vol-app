<?php

declare(strict_types=1);

/**
 * Validate Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CopyApplicationDataToLicence;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ValidateApplication as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\ValidateApplication;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\CreateFromGrant;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Validate Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ValidateApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ValidateApplication();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->updateTotAuthHgvVehicles(10);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setId(111);
        $application->setGoodsOrPsv($this->refData[Licence::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setRequestInspection(true);
        $application->setRequestInspectionDelay(3);
        $application->setRequestInspectionComment('foo');

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffect(CreateSnapshot::class, ['id' => 111, 'event' => CreateSnapshot::ON_GRANT], $result1);

        $result2 = new Result();
        $result2->addMessage('CopyApplicationDataToLicence');
        $this->expectedSideEffect(CopyApplicationDataToLicence::class, $data, $result2);

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffect(ProcessApplicationOperatingCentres::class, $data, $result3);

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $this->expectedSideEffect(CommonGrant::class, $data, $result4);

        $result5 = new Result();
        $result5->addMessage('CreateDiscRecords');
        $discData = $data;
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffect(CreateDiscRecords::class, $discData, $result5);

        $result6 = new Result();
        $result6->addMessage('InspectionRequestCreated');
        $irData = [
            'application' => 111,
            'duePeriod' => 3,
            'caseworkerNotes' => 'foo'
        ];
        $this->expectedSideEffect(CreateFromGrant::class, $irData, $result6);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateSnapshot',
                'CopyApplicationDataToLicence',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'CreateDiscRecords',
                'InspectionRequestCreated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
