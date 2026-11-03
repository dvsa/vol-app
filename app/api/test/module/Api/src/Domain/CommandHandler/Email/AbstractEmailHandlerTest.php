<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\AbstractEmailHandler;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class AbstractEmailHandlerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new class extends AbstractEmailHandler {
            public array $handlerVariables = [];

            protected $repoServiceName = 'IrhpApplication';
            protected $template = 'some-template';
            protected $subject = 'some.subject';

            protected function getRecipients($recordObject): array
            {
                return ['to' => 'to@test.com', 'cc' => [], 'bcc' => []];
            }

            protected function createMissingEmailTask(mixed $recordObject, Result $result, MissingEmailException $exception): Result
            {
                return $result;
            }

            protected function getTemplateVariables($recordObject)
            {
                return $this->handlerVariables;
            }
        };

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    private function runHandler(object $record, array $handlerVariables, array $expectedVariables): void
    {
        $this->sut->handlerVariables = $handlerVariables;

        $record->shouldReceive('getId')->withNoArgs()->once()->andReturn(77);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class))
            ->once()
            ->andReturn($record);

        // expected variables are the handler's own plus licNo, added by AbstractEmailHandler::handleCommand()
        $this->mockedSmServices[TemplateRenderer::class]
            ->shouldReceive('renderBody')
            ->with(m::type(Message::class), 'some-template', $expectedVariables, 'default')
            ->once();

        $this->expectedSideEffect(SendEmail::class, ['to' => 'to@test.com', 'locale' => 'en_GB', 'subject' => 'some.subject'], new Result());

        $this->sut->handleCommand(m::mock(CommandInterface::class));
    }

    public function testLicNoAddedForIrhpApplication(): void
    {
        $record = m::mock(IrhpApplication::class);
        $record->shouldReceive('getLicence->getLicNo')->withNoArgs()->once()->andReturn('OB1234567');

        $this->runHandler($record, ['foo' => 'bar'], ['foo' => 'bar', 'licNo' => 'OB1234567']);
    }

    public function testLicNoAddedForBusReg(): void
    {
        $record = m::mock(BusReg::class);
        $record->shouldReceive('getLicence->getLicNo')->withNoArgs()->once()->andReturn('OB1234567');

        $this->runHandler($record, [], ['licNo' => 'OB1234567']);
    }

    public function testLicNoAddedWhenRecordIsLicence(): void
    {
        $record = m::mock(Licence::class);
        $record->shouldReceive('getLicNo')->withNoArgs()->once()->andReturn('OB1234567');

        $this->runHandler($record, [], ['licNo' => 'OB1234567']);
    }

    public function testVariablesUntouchedWhenLicenceHasNoNumber(): void
    {
        $record = m::mock(Licence::class);
        $record->shouldReceive('getLicNo')->withNoArgs()->once()->andReturn(null);

        $this->runHandler($record, ['foo' => 'bar'], ['foo' => 'bar']);
    }

    public function testLicNoNotOverwrittenWhenHandlerSuppliesIt(): void
    {
        $record = m::mock(Licence::class);
        $record->shouldReceive('getLicNo')->withNoArgs()->once()->andReturn('OB1234567');

        $this->runHandler($record, ['licNo' => 'FROM_HANDLER'], ['licNo' => 'FROM_HANDLER']);
    }

    public function testVariablesUntouchedWhenNoLicenceReachable(): void
    {
        $record = m::mock(Organisation::class);

        $this->runHandler($record, ['foo' => 'bar'], ['foo' => 'bar']);
    }
}
