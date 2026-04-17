<?php

declare(strict_types=1);

/**
 * Licence History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\LicenceHistory;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Application\Application as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Doctrine\ORM\Query;

/**
 * Licence History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistoryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LicenceHistory();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockLicence = m::mock()
            ->shouldReceive('serialize')
            ->andReturn('licence')
            ->getMock();

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('serialize')
            ->with([])
            ->andReturn(['id' => 111])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('current')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('applied')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('refused')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('revoked')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('public-inquiry')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('disqualified')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('getOtherLicencesByType')
            ->with('held')
            ->andReturn([$mockLicence])
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($mockApplication)
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_CURRENT)
            ->andReturn('current')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_APPLIED)
            ->andReturn('applied')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_REFUSED)
            ->andReturn('refused')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_REVOKED)
            ->andReturn('revoked')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_PUBLIC_INQUIRY)
            ->andReturn('public-inquiry')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_DISQUALIFIED)
            ->andReturn('disqualified')
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with(OtherLicence::TYPE_HELD)
            ->andReturn('held')
            ->once();

        $expected = [
            'id' => 111,
            'otherLicences' => [
                'prevHasLicence' => ['licence'],
                'prevHadLicence' => ['licence'],
                'prevBeenRefused' => ['licence'],
                'prevBeenRevoked' => ['licence'],
                'prevBeenAtPi' => ['licence'],
                'prevBeenDisqualifiedTc' => ['licence'],
                'prevPurchasedAssets' => ['licence']
            ]
        ];
        $result = $this->sut->handleQuery($query);

        $this->assertEquals($result->serialize(), $expected);
    }
}
