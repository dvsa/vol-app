<?php

declare(strict_types=1);

/**
 * GetDocumentsForResponsibilities Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmResponsibilities;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\TmResponsibilities\GetDocumentsForResponsibilities as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportMangerApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TransportMangerLicenceRepo;
use Dvsa\Olcs\Transfer\Query\TmResponsibilities\GetDocumentsForResponsibilities as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetDocumentsForResponsibilities Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetDocumentsForResponsibilitiesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('TransportManagerApplication', TransportMangerApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TransportMangerLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQueryForTypeApplication(): void
    {
        $query = Query::create(
            [
                'type' => 'application',
                'licOrAppId' => 1,
                'transportManager' => 2
            ]
        );

        $tmApplicationMock = m::mock()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(3)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchForResponsibilities')
            ->with(1)
            ->once()
            ->andReturn($tmApplicationMock)
            ->getMock();

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('RESULT')
            ->getMock();

        $this->repoMap['Document']
            ->shouldReceive('fetchListForTmApplication')
            ->with(2, 3)
            ->andReturn([$mockDocument])
            ->once()
            ->getMock();

        $this->assertEquals(
            [
                'result' => ['RESULT'],
                'count'  => 1
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryForTypeLicence(): void
    {
        $query = Query::create(
            [
                'type' => 'licence',
                'licOrAppId' => 1,
                'transportManager' => 2
            ]
        );

        $tmLicenceMock = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(3)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchForResponsibilities')
            ->with(1)
            ->once()
            ->andReturn($tmLicenceMock)
            ->getMock();

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('RESULT')
            ->getMock();

        $this->repoMap['Document']
            ->shouldReceive('fetchListForTmLicence')
            ->with(2, 3)
            ->andReturn([$mockDocument])
            ->once()
            ->getMock();

        $this->assertEquals(
            [
                'result' => ['RESULT'],
                'count'  => 1
            ],
            $this->sut->handleQuery($query)
        );
    }
}
