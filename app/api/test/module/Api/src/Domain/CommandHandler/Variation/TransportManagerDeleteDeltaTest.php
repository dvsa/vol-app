<?php

declare(strict_types=1);

/**
 * TransportManagerDeleteDeltaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\TransportManagerDeleteDelta as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Variation\TransportManagerDeleteDelta as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TransportManagerLicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Mockery as m;

/**
 * TransportManagerDeleteDeltaTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerDeleteDeltaTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);
        $this->mockRepo('TransportManagerLicence', TransportManagerLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommandInvalidLicence(): void
    {
        $command = Command::create(['id' => 863, 'transportManagerLicenceIds' => [345,7621]]);

        $tml1 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence::class)->makePartial();
        $tml1->setLicence($this->getLicence(12));

        $licence = $this->getLicence(3213);
        $application = $this->getApplication($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);
        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchById')->with(345)->once()->andReturn($tml1);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['id' => 863, 'transportManagerLicenceIds' => [345,7621]]);

        $licence = $this->getLicence(6542);

        $tml1 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence::class)->makePartial();
        $tml1->setLicence($licence);
        $tml1->setTransportManager('TM-1');

        $tml2 = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence::class)->makePartial();
        $tml2->setLicence($licence);
        $tml2->setTransportManager('TM-1');

        $application = $this->getApplication($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);
        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchById')->with(345)->once()->andReturn($tml1);
        $this->repoMap['TransportManagerLicence']->shouldReceive('fetchById')->with(7621)->once()->andReturn($tml2);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')
            ->with(m::type(\Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication::class))
            ->twice()
            ->andReturnUsing(
                function ($tma) use ($application) {
                    $tma->setId(9871);
                    $this->assertSame($application, $tma->getApplication());
                    $this->assertSame('TM-1', $tma->getTransportManager());
                    $this->assertSame('D', $tma->getAction());
                }
            );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            [
                'id' => 1066,
                'section' => 'transportManagers'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);
        $this->assertSame(
            [
                'Transport manager application ID 9871 delete Delata created',
                'Transport manager application ID 9871 delete Delata created'
            ],
            $result->getMessages()
        );
    }

    /**
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    protected function getLicence(mixed $id = null): mixed
    {
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $status = new \Dvsa\Olcs\Api\Entity\System\RefData();
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, $status);
        $licence->setId($id);

        return $licence;
    }

    /**
     * @return \Dvsa\Olcs\Api\Entity\Application\Application
     */
    protected function getApplication(mixed $licence = null): mixed
    {
        if ($licence === null) {
            $licence = $this->getLicence();
        }

        $status = new \Dvsa\Olcs\Api\Entity\System\RefData();
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, $status, false);
        $application->setId(1066);

        return $application;
    }
}
