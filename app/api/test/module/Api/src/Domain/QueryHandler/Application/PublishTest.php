<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Publish;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Query\Application\Publish as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * PublishTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Publish();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            'ApplicationPublishValidationService' => m::mock(),
            'VariationPublishValidationService' => m::mock(),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 2409]);

        /* @var $application ApplicationEntity */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setIsVariation(false);

        $application->shouldReceive('hasActiveS4')->with()->once()->andReturn(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['ApplicationPublishValidationService']->shouldReceive('validate')->with($application)
            ->once()->andReturn(['ERROR']);
        $application->shouldReceive('getPublicationLinks')->with()->once()
            ->andReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $application->shouldReceive('serialize')->with([])->once()
            ->andReturn(['SERIALIZED']);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'SERIALIZED',
            'errors' => ['ERROR'],
            'existingPublication' => false,
            'hasActiveS4' => true,
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
