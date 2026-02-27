<?php

declare(strict_types=1);

/**
 * Documents Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Tm;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Tm\Documents as QueryHandler;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Transfer\Query\Tm\Documents as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Documents Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DocumentsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Document', DocumentRepo::class);
        $mockedAuth = m::mock(AuthorizationService::class)->makePartial();
        $this->mockedSmServices[AuthorizationService::class] = $mockedAuth;

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn('foo')
            ->once()
            ->getMock();

        $this->repoMap['Document']
            ->shouldReceive('fetchListForTm')
            ->with(1)
            ->once()
            ->andReturn([$mockDocument])
            ->getMock();

        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->andReturn($mockedId);

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryReadOnlyUser(): void
    {
        $query = Query::create(['id' => 1]);

        $mockedRole = m::mock(Role::class);
        $mockedRole->shouldReceive('getRole')->andReturn(Role::ROLE_INTERNAL_LIMITED_READ_ONLY);
        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([$mockedRole]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->andReturn($mockedId);

        $this->assertSame(
            [
                'result'    => null,
                'count'     => 0,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
