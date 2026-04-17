<?php

declare(strict_types=1);

/**
 * CreatePreviousLicenceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\CreatePreviousLicence as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreatePreviousLicence as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * CreatePreviousLicenceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreatePreviousLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('OtherLicence', OtherLicence::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'tmaId' => 12,
                'holderName' => 'George',
                'licNo' => 'LIC001',
            ]
        );

        $tma = new TransportManagerApplication();
        $tma->setTransportManager('TRANSPORT_MANAGER');

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(12)->once()->andReturn($tma);
        $this->repoMap['OtherLicence']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (OtherLicenceEntity $ol) {
                    $ol->setId(452);
                    $this->assertSame('TRANSPORT_MANAGER', $ol->getTransportManager());
                    $this->assertSame('LIC001', $ol->getLicNo());
                    $this->assertSame('George', $ol->getHolderName());
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['otherLicence' => 452], $response->getIds());
        $this->assertSame(['Other Licence ID 452 created'], $response->getMessages());
    }
}
