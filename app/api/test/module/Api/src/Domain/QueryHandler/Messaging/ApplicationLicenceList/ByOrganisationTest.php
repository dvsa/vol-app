<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList;

use ArrayIterator;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList\ByOrganisation;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByOrganisation as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class ByOrganisationTest extends QueryHandlerTestCase
{
    /**
     * @var ByOrganisation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ByOrganisation();
        $this->mockRepo(Repository\Application::class, Repository\Application::class);
        $this->mockRepo(Repository\Licence::class, Repository\Licence::class);

        $this->mockedSmServices = [
            'SectionAccessService' => m::mock(),
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')
                ->with(Permission::SELFSERVE_USER, null)
                ->andReturn(true)
                ->shouldReceive('isGranted')
                ->with(Permission::INTERNAL_USER, null)
                ->andReturn(false)
                ->getMock(),
            ];

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser->getId')
            ->andReturn(1);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([
            'organisation' => 1,
        ]);

        $licences = new ArrayIterator(
            [
                [ 'id' => 1, 'organisation_id' => 1, 'licNo' => 'A1' ],
                [ 'id' => 2, 'organisation_id' => 1, 'licNo' => 'B2' ],
                [ 'id' => 3, 'organisation_id' => 1, 'licNo' => 'C3' ],
            ]
        );

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')
                ->times(6)
                ->andReturn('ABC');
        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app1->shouldReceive('getId')
             ->twice()
             ->andReturn(3);
        $app2 = m::mock(Application::class);
        $app2->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app2->shouldReceive('getId')
             ->twice()
             ->andReturn(4);
        $app3 = m::mock(Application::class);
        $app3->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app3->shouldReceive('getId')
             ->twice()
             ->andReturn(5);

        $applications = new ArrayIterator(
            [
                $app1,
                $app2,
                $app3,
            ]
        );

        $mockQb = m::mock(QueryBuilder::class);

        $this->repoMap[Repository\Licence::class]->shouldReceive('fetchByOrganisationIdAndStatuses')->andReturn($mockQb)->once()->andReturn($licences);
        $this->repoMap[Repository\Application::class]->shouldReceive('fetchByOrganisationIdAndStatuses')->andReturn($mockQb)->once()->andReturn($applications);

        $result = $this->sut->handleQuery($query);

        $this->assertArrayHasKey('result', $result);
        $this->assertCount(2, $result['result']);

        $this->assertArrayHasKey('licences', $result['result']);
        $this->assertCount(3, $result['result']['licences']);

        $this->assertArrayHasKey('applications', $result['result']);
        $this->assertCount(3, $result['result']['applications']);
    }

    public function testHandleQueryDefersToIdentityIfNoOrganisationIsDefinedInQuery(): void
    {
        $organisation = m::mock(Organisation::class);
        $organisation
            ->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $query = Qry::create([]);

        $licences = new ArrayIterator(
            [
                [ 'id' => 1, 'organisation_id' => 1, 'licNo' => 'A1' ],
                [ 'id' => 2, 'organisation_id' => 1, 'licNo' => 'B2' ],
                [ 'id' => 3, 'organisation_id' => 1, 'licNo' => 'C3' ],
            ]
        );

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')
                ->times(6)
                ->andReturn('ABC');
        $app1 = m::mock(Application::class);
        $app1->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app1->shouldReceive('getId')
             ->twice()
             ->andReturn(3);
        $app2 = m::mock(Application::class);
        $app2->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app2->shouldReceive('getId')
             ->twice()
             ->andReturn(4);
        $app3 = m::mock(Application::class);
        $app3->shouldReceive('getLicence')
             ->times(3)
             ->andReturn($licence);
        $app3->shouldReceive('getId')
             ->twice()
             ->andReturn(5);

        $applications = new ArrayIterator(
            [
                $app1,
                $app2,
                $app3,
            ]
        );

        $mockQb = m::mock(QueryBuilder::class);

        $this->repoMap[Repository\Licence::class]->shouldReceive('fetchByOrganisationIdAndStatuses')->andReturn($mockQb)->once()->andReturn($licences);
        $this->repoMap[Repository\Application::class]->shouldReceive('fetchByOrganisationIdAndStatuses')->andReturn($mockQb)->once()->andReturn($applications);

        $result = $this->sut->handleQuery($query);

        $this->assertArrayHasKey('result', $result);
        $this->assertCount(2, $result['result']);

        $this->assertArrayHasKey('licences', $result['result']);
        $this->assertCount(3, $result['result']['licences']);

        $this->assertArrayHasKey('applications', $result['result']);
        $this->assertCount(3, $result['result']['applications']);
    }
}
