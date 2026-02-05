<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerLicence\GetListByVariation as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as Repo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetListByVariation as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetListByVariation test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetListByVariationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManagerLicence', Repo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    /**
     *
     * @param $licenceType Application licence type id . eg Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $licenceType, mixed $expected): void
    {
        $query = Query::create(['variation' => 1]);

        $mockTml = m::mock();
        $mockApplication = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(2)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();
        $mockApplication->shouldReceive('getLicenceType->getId')->with()->once()->andReturn($licenceType);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with(1)
            ->once()
            ->andReturn($mockApplication);

        $this->repoMap['TransportManagerLicence']
            ->shouldReceive('fetchByLicence')
            ->with(2)
            ->once()
            ->andReturn([$mockTml]);

        $mockTml->shouldReceive('serialize')->with(
            [
                'licence' => [
                    'status',
                    'licenceType',
                ],
                'transportManager' => [
                    'homeCd' => [
                        'person',
                    ],
                    'tmType',
                ]
            ]
        )->once()->andReturn('RESULT');

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }

    public static function dpHandleQuery(): array
    {
        return [
            [
                'xxx',
                ['result' => ['RESULT'], 'count' => 1, 'requiresSiQualification' => false]
            ],
            [
                \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ['result' => ['RESULT'], 'count' => 1, 'requiresSiQualification' => true]
            ],
        ];
    }
}
