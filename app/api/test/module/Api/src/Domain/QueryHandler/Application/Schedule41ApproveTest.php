<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Publish;
use Dvsa\Olcs\Transfer\Query\Application\Schedule41Approve as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Schedule41ApproveTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Schedule41ApproveTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Domain\QueryHandler\Application\Schedule41Approve();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            'FeesHelperService' => m::mock(),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 510]);

        /* @var $application ApplicationEntity */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(510);
        $application->setLicenceType(
            new \Dvsa\Olcs\Api\Entity\System\RefData(
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        );

        $applicationCompletion = new \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion($application);
        $applicationCompletion->setOperatingCentresStatus(2);
        $applicationCompletion->setTransportManagersStatus(2);
        $application->setApplicationCompletion($applicationCompletion);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['FeesHelperService']->shouldReceive('getOutstandingFeesForApplication')
            ->with(510)->once()->andReturn();

        $application->shouldReceive('serialize')->with([])->once()
            ->andReturn(['SERIALIZED']);

        $result = $this->sut->handleQuery($query);
        $resultArray = $result->serialize();

        $this->assertCount(0, $resultArray['errors']);
    }

    public function testHandleQueryErrors(): void
    {
        $query = Qry::create(['id' => 510]);

        /* @var $application ApplicationEntity */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(510);
        $application->setLicenceType(
            new \Dvsa\Olcs\Api\Entity\System\RefData(
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        );

        $applicationCompletion = new \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion($application);
        $application->setApplicationCompletion($applicationCompletion);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['FeesHelperService']->shouldReceive('getOutstandingFeesForApplication')
            ->with(510)->once()->andReturn('ERROR');

        $application->shouldReceive('serialize')->with([])->once()
            ->andReturn(['SERIALIZED']);

        $result = $this->sut->handleQuery($query);
        $resultArray = $result->serialize();

        $this->assertCount(3, $resultArray['errors']);
        $this->assertArrayHasKey('S41_APP_OUSTANDING_FEE', $resultArray['errors']);
        $this->assertArrayHasKey('S41_APP_APPROVE_TM', $resultArray['errors']);
        $this->assertArrayHasKey('S41_APP_APPROVE_OC', $resultArray['errors']);
    }

    public function testHandleQueryErrorsVariation(): void
    {
        $query = Qry::create(['id' => 510]);

        /* @var $application ApplicationEntity */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(510);
        $application->setIsVariation(true);
        $application->setLicenceType(
            new \Dvsa\Olcs\Api\Entity\System\RefData(
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            )
        );

        $applicationCompletion = new \Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion($application);
        $application->setApplicationCompletion($applicationCompletion);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['FeesHelperService']->shouldReceive('getOutstandingFeesForApplication')
            ->with(510)->once()->andReturn('ERROR');

        $application->shouldReceive('serialize')->with([])->once()
            ->andReturn(['SERIALIZED']);

        $result = $this->sut->handleQuery($query);
        $resultArray = $result->serialize();

        $this->assertCount(2, $resultArray['errors']);
        $this->assertArrayHasKey('S41_APP_OUSTANDING_FEE', $resultArray['errors']);
        $this->assertArrayHasKey('S41_APP_APPROVE_OC', $resultArray['errors']);
    }
}
