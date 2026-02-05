<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\OperatorSigned as CommandHandler;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateStatus as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot;

/**
 * OperatorSignedTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatorSignedTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo(
            'TransportManagerApplication',
            \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication::class
        );
        $this->mockRepo(
            'TransportManager',
            \Dvsa\Olcs\Api\Domain\Repository\TransportManager::class
        );

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TransportManagerApplication::STATUS_OPERATOR_SIGNED,
            TransportManagerApplication::TYPE_EXTERNAL,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithVersion(): void
    {
        $command = Command::create(['id' => 863, 'version' => 234]);

        $mockTransportManager = m::mock(TransportManager::class)
            ->shouldReceive('getTmType')
            ->andReturnNull()
            ->once()
            ->shouldReceive('setTmType')
            ->with($this->refData[TransportManagerApplication::TYPE_EXTERNAL])
            ->once()
            ->shouldReceive('getHomeCd')
            ->andReturn(
                m::mock()
                ->shouldReceive('getEmailAddress')
                ->andReturn('email1')
                ->twice()
                ->getMock()
            )
            ->twice()
            ->shouldReceive('getId')
            ->andReturn(123)
            ->once()
            ->getMock();

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setId(12);
        $tma->setTmType(TransportManagerApplication::TYPE_EXTERNAL);
        $tma->setTransportManager($mockTransportManager);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManagerApplication $tma) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::STATUS_OPERATOR_SIGNED],
                    $tma->getTmApplicationStatus()
                );
            }
        );
        $this->repoMap['TransportManager']->shouldReceive('save')->once();

        $data = [
            'id' => 12,
            'user' => 123
        ];

        $result = new Result();
        $this->expectedSideEffect(Snapshot::class, $data, $result);

        $this->assertEmailSent($tma);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithoutVersion(): void
    {
        $command = Command::create(['id' => 863]);

        $mockTransportManager = m::mock(TransportManager::class)
            ->shouldReceive('getTmType')
            ->andReturnNull()
            ->once()
            ->shouldReceive('setTmType')
            ->with($this->refData[TransportManagerApplication::TYPE_EXTERNAL])
            ->once()
            ->shouldReceive('getHomeCd')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getEmailAddress')
                    ->andReturnNull()
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->twice()
            ->getMock();

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setId(12);
        $tma->setTmType(TransportManagerApplication::TYPE_EXTERNAL);
        $tma->setTransportManager($mockTransportManager);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TransportManagerApplication $tma) {
                $this->assertSame(
                    $this->refData[TransportManagerApplication::STATUS_OPERATOR_SIGNED],
                    $tma->getTmApplicationStatus()
                );
            }
        );
        $this->repoMap['TransportManager']->shouldReceive('save')->once();

        $data = [
            'id' => 12,
            'user' => 1
        ];

        $result = new Result();
        $this->expectedSideEffect(Snapshot::class, $data, $result);

        $this->sut->handleCommand($command);
    }

    private function assertEmailSent(mixed $tma): void
    {
        $tma->shouldReceive('getApplication->getLicence->getTranslateToWelsh')->with()->once()->andReturn('Y');
        $tma->shouldReceive('getApplication->getLicence->getOrganisation->getName')->with()->once()
            ->andReturn('ORG_NAME');
        $tma->shouldReceive('getApplication->getLicence->getLicNo')->with()->once()->andReturn('LIC01');
        $tma->shouldReceive('getApplication->getId')->with()->twice()->andReturn(76);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Email\Data\Message $message, $template, $vars, $layout) {

                $this->assertSame('email.transport-manager-confirmed.subject', $message->getSubject());
                $this->assertSame('email1', $message->getTo());
                $this->assertSame('cy_GB', $message->getLocale());

                $this->assertSame('transport-manager-confirmed', $template);
                $this->assertSame(
                    [
                        'operatorName' => 'ORG_NAME',
                        'licNo' => 'LIC01',
                        'applicationId' => 76,
                        'tmaUrl' => 'http://selfserve/application/76/transport-managers/details/12/'
                    ],
                    $vars
                );
                $this->assertSame('default', $layout);
            }
        );

        $result = new Result();
        $data = [
            'to' => 'email1'
        ];
        $this->expectedSideEffect(SendEmail::class, $data, $result);
    }
}
