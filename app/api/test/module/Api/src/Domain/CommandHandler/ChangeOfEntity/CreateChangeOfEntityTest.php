<?php

declare(strict_types=1);

/**
 * Create Change Of Entity test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ChangeOfEntity;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\ChangeOfEntity\CreateChangeOfEntity as Sut;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ChangeOfEntity as ChangeOfEntityRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\CreateChangeOfEntity as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity as ChangeOfEntityEntity;

/**
 * Create Change Of Entity test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateChangeOfEntityTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('ChangeOfEntity', ChangeOfEntityRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'applicationId' => 69,
                'oldLicenceNo' => 'AB1234',
                'oldOrganisationName' => 'Foo',
            ]
        );

        /** @var ChangeOfEntityEntity $changeOfEntity */
        $changeOfEntity = null;

        $this->repoMap['ChangeOfEntity']
            ->shouldReceive('save')
            ->with(m::type(ChangeOfEntityEntity::class))
            ->andReturnUsing(
                function (ChangeOfEntityEntity $change) use (&$changeOfEntity) {
                    $changeOfEntity = $change;
                    $changeOfEntity->setId(99);
                }
            )
            ->once();

        $mockApplication = m::mock(ApplicationEntity::class)->makePartial();
        $mockLicence = m::mock(LicenceEntity::class)->makePartial();
        $mockLicence->setId(7);
        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with(69)
            ->once()
            ->andReturn($mockApplication);
        $mockApplication
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('ChangeOfEntity Created', $result->getMessages());
        $this->assertEquals(99, $result->getId('changeOfEntity'));
        $this->assertSame($mockLicence, $changeOfEntity->getLicence());
    }
}
