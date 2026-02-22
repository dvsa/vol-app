<?php

declare(strict_types=1);

/**
 * Update Total Community Licences Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateTotalCommunityLicences;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as Cmd;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Update Total Community Licences Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTotalCommunityLicencesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTotalCommunityLicences();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 1]);

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('updateTotalCommunityLicences')
            ->with(1)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('save')
            ->with($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->with(1)
            ->andReturn(['foo'])
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Total community licences count updated']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}
