<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\People;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Licence\People as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Licence\People::class)]
class PeopleTest extends QueryHandlerTestCase
{
    /** @var People */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new People();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')->andReturn(false)->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 111]);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation
            ->setId(923)
            ->setType(new \Dvsa\Olcs\Api\Entity\System\RefData());

        $licence = new Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $licence->setId(432);

        $mockOp = m::mock()
            ->shouldReceive('serialize')->with(['person' => ['title']])->once()->andReturn(['OP'])
            ->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($licence);
        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisation')->with(923)
            ->andReturn([$mockOp]);

        $response = $this->sut->handleQuery($query);
        $responseArray = $response->serialize();

        $this->assertEquals(432, $responseArray['id']);
        $this->assertFalse($responseArray['hasInforceLicences']);
        $this->assertArrayHasKey('people', $responseArray);
        $this->assertCount(1, $responseArray['people']);
        $this->assertEquals(['OP'], $responseArray['people'][0]);
    }
}
