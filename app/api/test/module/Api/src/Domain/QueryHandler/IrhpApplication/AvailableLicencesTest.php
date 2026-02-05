<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\AvailableLicences;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AvailableLicences as AvailableLicencesQry;
use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationAvailableLicences;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use LmcRbacMvc\Identity\IdentityInterface;

class AvailableLicencesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AvailableLicences();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryProvider')]
    public function testHandleQuery(mixed $isMultiStock, mixed $stockId): void
    {
        $permitTypeId = 777;
        $licenceId = 666;
        $isNotYetSubmitted = true;
        $originalQuery = m::mock(AvailableLicencesQry::class);

        $permitType = m::mock(IrhpPermitType::class);
        $permitType->shouldReceive('getId')->once()->withNoArgs()->andReturn($permitTypeId);
        $permitType->shouldReceive('isMultiStock')->once()->withNoArgs()->andReturn($isMultiStock);

        $application = m::mock(IrhpApplication::class);
        $application->shouldReceive('getIrhpPermitType')->once()->withNoArgs()->andReturn($permitType);
        $application->shouldReceive('getAssociatedStock->getId')
            ->times($isMultiStock ? 0 : 1)
            ->withNoArgs()
            ->andReturn($stockId);
        $application->shouldReceive('getLicence->getId')->once()->withNoArgs()->andReturn($licenceId);
        $application->shouldReceive('isNotYetSubmitted')->once()->withNoArgs()->andReturn($isNotYetSubmitted);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($originalQuery)
            ->andReturn($application);

        $orgId = 1245;
        $organisation = m::mock(OrganisationEntity::class);
        $organisation->shouldReceive('getId')->andReturn($orgId);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $identity->shouldReceive('getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $queryResult = ['eligibleLicences' => ['licences']];

        $this->queryHandler->shouldReceive('handleQuery')
            ->andReturnUsing(
                function ($organisationPermitsQry) use ($queryResult, $orgId, $permitTypeId, $stockId) {
                    /** @var OrganisationAvailableLicences $organisationPermitsQry */
                    $this->assertInstanceOf(OrganisationAvailableLicences::class, $organisationPermitsQry);
                    $data = $organisationPermitsQry->getArrayCopy();

                    $this->assertEquals($orgId, $data['id']);
                    $this->assertEquals($permitTypeId, $data['irhpPermitType']);
                    $this->assertEquals($stockId, $data['irhpPermitStock']);

                    return $queryResult;
                }
            );

        $additionalValues = [
            'selectedLicence' => $licenceId,
            'isNotYetSubmitted' => $isNotYetSubmitted,
        ];

        $expectedReturnValues = array_merge($queryResult, $additionalValues);

        $this->assertEquals($expectedReturnValues, $this->sut->handleQuery($originalQuery));
    }

    public static function dpHandleQueryProvider(): array
    {
        return [
            [true, null],
            [false, 111]
        ];
    }
}
