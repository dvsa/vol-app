<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CommunityLic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\CommunityLic\CommunityLicences as CommunityLicencesQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences as Qry;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Doctrine\ORM\Query;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery\MockInterface;
use Dvsa\OlcsTest\MocksRepositoriesTrait;
use Dvsa\OlcsTest\MocksServicesTrait;
use Dvsa\OlcsTest\Api\Domain\Repository\MocksLicenceRepositoryTrait;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicenceEntity;
use Dvsa\OlcsTest\Api\Entity\Licence\LicenceBuilder;
use Dvsa\Olcs\Api\Entity\System\RefData;

class CommunityLicencesTest extends QueryHandlerTestCase
{
    use MocksRepositoriesTrait;
    use MocksServicesTrait;
    use MocksLicenceRepositoryTrait;

    public function testHandleQueryIsDefined(): void
    {
        // Assert
        $this->assertIsCallable($this->sut->handleQuery(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('testHandleQueryIsDefined')]
    public function testHandleQuery(): void
    {
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        parent::setUp();

        $licenceId = 1;
        $query = Qry::create(['licence' => $licenceId]);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getTotCommunityLicences')
            ->andReturn(2)
            ->once()
            ->getMock()
            ->shouldIgnoreMissing();

        $mockLicence->shouldReceive('getId')->andReturn(1);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $mockOfficeCopy = m::mock(BundleSerializableInterface::class)->shouldReceive('serialize')->andReturn(['item'])->getMock();
        $mockComLic = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('result')
            ->getMock();
        $comLics = new ArrayCollection();
        $comLics->add($mockComLic);

        $this->repoMap['CommunityLic']->shouldReceive('fetchOfficeCopy')
            ->with($licenceId)
            ->andReturn($mockOfficeCopy)
            ->once()
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($comLics)
            ->once()
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(15)
            ->shouldReceive('hasRows')
            ->with(m::type(Qry::class))
            ->andReturn(1)
            ->once()
            ->getMock()
            ->shouldIgnoreMissing();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => ['result'],
            'count' =>  15,
            'count-unfiltered' => 1,
            'totCommunityLicences' => 2,
            'totActiveCommunityLicences' => 0,
            'officeCopy' => ['item']
        ];

        $this->assertEquals($result, $expected);
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
        $this->sut = new CommunityLicencesQueryHandler();
        parent::setUp();
    }

    public function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->repositoryServiceManager();
        $this->licenceRepository();
        $this->communityLicenceRepository();
        $serviceManager->setService(AuthorizationService::class, $this->setUpAuthorizationService());
    }

    /**
     * @return MockInterface|CommunityLicRepo
     */
    protected function communityLicenceRepository(): MockInterface
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('CommunityLic')) {
            $instance = $this->setUpMockService(CommunityLicRepo::class);
            $instance->allows('fetchOfficeCopy')->andReturnUsing(function (int $id) {
                $entity = new CommunityLicenceEntity();
                $entity->setStatus(new RefData(CommunityLicenceEntity::STATUS_ACTIVE));
                $entity->setId($id);
                return $entity;
            })->byDefault();
            $instance->allows('fetchList')->andReturn([])->byDefault();
            $instance->allows('fetchCount')->andReturn(0)->byDefault();
            $instance->allows('hasRows')->andReturn(true)->byDefault();
            $instance->allows('countActiveByLicenceId')->andReturn(0)->byDefault();
            $repositoryServiceManager->setService('CommunityLic', $instance);
        }
        return $repositoryServiceManager->get('CommunityLic');
    }

    /**
     * @return MockInterface|AuthorizationService
     */
    protected function setUpAuthorizationService(): MockInterface
    {
        $service = m::mock(AuthorizationService::class);
        $service->shouldReceive('isGranted')->andReturn(false)->byDefault();
        return $service;
    }
}
