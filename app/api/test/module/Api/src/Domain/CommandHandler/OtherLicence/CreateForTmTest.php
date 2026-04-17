<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\CreateForTm as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateForTm as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;

/**
 * CreateForTmTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateForTmTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('OtherLicence', OtherLicence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            TransportManager::class => [
                64 => m::mock(TransportManager::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'licNo' => 'LIC001',
                'holderName' => 'JOHN',
                'transportManagerId' => 64
            ]
        );

        $this->repoMap['OtherLicence']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (OtherLicenceEntity $ol) {
                    $ol->setId(422);
                    $this->assertSame(
                        $this->references[TransportManager::class][64],
                        $ol->getTransportManager()
                    );
                    $this->assertSame('JOHN', $ol->getHolderName());
                    $this->assertSame('LIC001', $ol->getLicNo());
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['otherLicence' => 422], $response->getIds());
        $this->assertSame(['Other Licence ID 422 created'], $response->getMessages());
    }
}
