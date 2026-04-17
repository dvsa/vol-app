<?php

declare(strict_types=1);

/**
 * Transport Manager Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Tm;

use Dvsa\Olcs\Api\Domain\QueryHandler\Tm\TransportManager as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Tm\TransportManager as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepo;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\OlcsTest\Api\Entity\User as UserEntity;

/**
 * Transport Manager Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TransportManager', TransportManagerRepo::class);
        $this->mockRepo('Note', NoteRepo::class);

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
        $latestNote = 'test note';
        $tmId = 1;
        $bundle = [
            'tmType',
            'tmStatus',
            'homeCd' => [
                'person' => [
                    'title'
                ],
                'address' => [
                    'countryCode'
                ]
            ],
            'workCd' => [
                'address' => [
                    'countryCode'
                ]
            ],
            'users',
            'mergeToTransportManager' => [
                'homeCd' => ['person']
            ]
        ];
        $query = Query::create(['id' => $tmId]);

        $mock = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('getUsers')
            ->andReturn([1,2,3])
            ->shouldReceive('getMergeToTransportManager')
            ->andReturn(null)
            ->shouldReceive('serialize')->with($bundle)
            ->once()
            ->andReturn(['foo'])
            ->shouldReceive('getId')
            ->andReturn($tmId)
            ->getMock();

        $this->repoMap['TransportManager']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mock);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with(null, null, $tmId)->andReturn($latestNote);

        $this->assertSame(
            ['foo', 'hasUsers' => [1,2,3], 'hasBeenMerged' => false, 'latestNote' => 'test note'],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
