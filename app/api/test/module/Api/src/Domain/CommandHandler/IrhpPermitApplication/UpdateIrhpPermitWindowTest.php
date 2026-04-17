<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitApplication;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitApplication\UpdateIrhpPermitWindow;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitApplication\UpdateIrhpPermitWindow as UpdateIrhpPermitWindowHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create Replacement IRHP Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class UpdateIrhpPermitWindowTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateIrhpPermitWindowHandler();
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        parent::setUp();
    }

    /**
     * Test
     */
    public function testHandleCommand(): void
    {
        $cmdData = [
            'id' => '3658',
            'irhpPermitWindow' => '7'
        ];

        $command = UpdateIrhpPermitWindow::create($cmdData);

        /** @var IrhpPermitWindow $irhpPermitWindow */
        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);

        /** @var IrhpApplication $irhpApplication */
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($irhpPermitApplication);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitWindow'])
            ->andReturn($irhpPermitWindow);

        $irhpPermitApplication->shouldReceive('updateIrhpPermitWindow')
            ->once()
            ->with($irhpPermitWindow);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('save')
            ->once()
            ->with($irhpPermitApplication);

        $irhpPermitApplication->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn($cmdData['id']);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['irhpPermitApplication' => $cmdData['id']],
            'messages' => ['IrhpPermitApplication Updated']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
