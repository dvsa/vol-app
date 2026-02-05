<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\CreateForIrhpApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitApplication\CreateForIrhpApplication as CreateForIrhpApplicationHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create Replacement IRHP Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateForIrhpApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateForIrhpApplicationHandler();
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    /**
     * Test
     */
    public function testHandleCommand(): void
    {
        $cmdData = [
            'irhpApplication' => '10000',
            'irhpPermitWindow' => '7'
        ];

        $command = CreateForIrhpApplication::create($cmdData);

        /** @var IrhpPermitWindow $irhpPermitWindow */
        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);

        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = m::mock(IrhpApplication::class)->makePartial();

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(7);

        $irhpApplication->setLicence($licence);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitWindow'])
            ->andReturn($irhpPermitWindow);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpApplication'])
            ->andReturn($irhpApplication);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpPermitApplicationEntity::class))
            ->andReturnUsing(
                function (IrhpPermitApplicationEntity $irhpPermitApplication) use (&$savedIrhpPermitApplication) {
                    $irhpPermitApplication->setId(3547);
                    $savedIrhpPermitApplication = $irhpPermitApplication;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['irhpPermitApplication' => 3547],
            'messages' => ['IrhpPermitApplication Created']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
