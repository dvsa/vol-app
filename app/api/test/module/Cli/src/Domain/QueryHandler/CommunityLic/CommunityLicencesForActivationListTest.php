<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\QueryHandler\CommunityLic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Cli\Domain\QueryHandler\CommunityLic\CommunityLicencesForActivationList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForActivationList as Qry;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Mockery as m;

/**
 * Community licences for activation list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicencesForActivationListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommunityLicencesForActivationList();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $now = (new \DateTime())->setTime(0, 0, 0);
        $query = Qry::create(['date' => $now]);

        $mockComLic = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('result')
            ->getMock();
        $comLics = new ArrayCollection();
        $comLics->add($mockComLic);

        $this->repoMap['CommunityLic']->shouldReceive('fetchForActivation')
            ->with($now)
            ->andReturn($comLics)
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => ['result'],
            'count' =>  1
        ];

        $this->assertEquals($result, $expected);
    }
}
