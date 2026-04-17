<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\People;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\Application\People as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Application\People::class)]
class PeopleTest extends QueryHandlerTestCase
{
    /** @var  People */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new People();

        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);
        $this->mockRepo('ApplicationOrganisationPerson', Repository\ApplicationOrganisationPerson::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $orgId = 923;
        $appId = 111;

        $query = Query::create(['id' => $appId]);

        $organisation = new Entity\Organisation\Organisation();
        $organisation->setId($orgId)->setType(new Entity\System\RefData());

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new Entity\System\RefData());
        $licence->setId(432);

        $application = new Entity\Application\Application(
            $licence,
            new Entity\System\RefData(),
            1
        );
        $application->setId($appId);

        $mockOp = m::mock()
            ->shouldReceive('serialize')->with(['person' => ['title']])->once()->andReturn(['OP'])
            ->getMock();

        $mockAop = m::mock()
            ->shouldReceive('serialize')->with(['person', 'originalPerson'])->once()->andReturn(['AOP'])
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);
        $this->repoMap['OrganisationPerson']
            ->shouldReceive('fetchListForOrganisation')->with($orgId)->andReturn([$mockOp]);
        $this->repoMap['ApplicationOrganisationPerson']
            ->shouldReceive('fetchListForApplication')->with($appId)->andReturn([$mockAop]);

        $this->repoMap['Licence']
            ->shouldReceive('fetchByOrganisationIdAndStatuses')
            ->with(
                $orgId,
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_SURRENDERED,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                ]
            )
            ->andReturn(['foo', 'bar'])
            ->once()
            ->getMock();

        $response = $this->sut->handleQuery($query);
        $responseArray = $response->serialize();

        $this->assertIsArray($responseArray);
        $this->assertEquals(111, $responseArray['id']);
        $this->assertFalse($responseArray['hasInforceLicences']);
        $this->assertFalse($responseArray['isExceptionalType']);
        $this->assertFalse($responseArray['isSoleTrader']);
        $this->assertTrue($responseArray['hasMoreThanOneValidCurtailedOrSuspendedLicences']);

        $this->assertIsArray($responseArray['people']);
        $this->assertCount(1, $responseArray['people']);
        $this->assertEquals(['OP'], $responseArray['people'][0]);

        $this->assertIsArray($responseArray['application-people']);
        $this->assertCount(1, $responseArray['application-people']);
        $this->assertEquals(['AOP'], $responseArray['application-people'][0]);
    }
}
