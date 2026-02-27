<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\EnforcementArea\PostcodeEnforcementArea;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\PostcodeEnforcementArea as PostcodeEnforcementAreaRepo;

class PostcodeEnforcementAreaTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(PostcodeEnforcementAreaRepo::class);
    }

    public function testFetchByPostcodeId(): void
    {
        $postcodeId = 'AB1';

        $postcodeEnforcementAreaMock = m::mock(PostcodeEnforcementArea::class);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('findOneBy')
            ->with(['postcodeId' => 'AB1'])
            ->andReturn($postcodeEnforcementAreaMock);

        $this->em->shouldReceive('getRepository')
            ->with(PostcodeEnforcementArea::class)
            ->andReturn($repo);

        $this->assertSame($postcodeEnforcementAreaMock, $this->sut->fetchByPostcodeId($postcodeId));
    }
}
