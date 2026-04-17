<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\BulkSend\ProcessEmail as SendEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend\ProcessEmail as ProcessEmailHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Test Bulk Send email
 */
class ProcessEmailTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessEmailHandler();

        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
            'translator' => m::mock(Translator::class),
        ];

        parent::setUp();
    }

    /**
     * test handle command
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $templateName, mixed $subject): void
    {
        $templateVars = [
            'licenceType' => 'Restricted',
            'goodsOrPsv' => 'Goods',
        ];

        $licenceEntity = m::mock(Licence::class);
        $organisation = m::mock(Organisation::class);

        $command = SendEmailCmd::create(['id' => 7, 'templateName' => $templateName]);

        $licenceEntity->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(7);

        $licenceEntity->shouldReceive('getOrganisation')
            ->once()
            ->withNoArgs()
            ->andReturn($organisation);

        $licenceEntity->shouldReceive('getLicenceType->getDescription')
            ->once()
            ->withNoArgs()
            ->andReturn('Restricted');

        $licenceEntity->shouldReceive('getGoodsOrPsv->getDescription')
            ->once()
            ->withNoArgs()
            ->andReturn('Goods');

        $licenceEntity->shouldReceive('getTranslateToWelsh')->andReturn(false);

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($licenceEntity);

        $organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn(['email@org.com']);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $templateName,
            $templateVars,
            'default'
        );

        $expectedData = [
            'to' => 'email@org.com',
            'locale' => 'en_GB',
            'subject' => $subject,
        ];

        $this->expectedSideEffect(SendEmail::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame('email@org.com', $message->getTo());
        $this->assertSame([], $message->getCc());
        $this->assertSame($subject, $message->getSubject());
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            ['unknown-template', 'Important information about your vehicle operator licence'],
            ['report-gv-r', 'Important information about your goods vehicle licence'],
            ['report-psv-r', 'Important information about your PSV licence']
        ];
    }

    /**
     * test handle command no emails for organisation
     */
    public function testHandleCommandNoEmails(): void
    {
        $licenceEntity = m::mock(Licence::class);
        $organisation = m::mock(Organisation::class);

        $command = SendEmailCmd::create(['id' => 7, 'templateName' => 'report-gv-r']);

        $licenceEntity->shouldReceive('getId')
            ->twice()
            ->withNoArgs()
            ->andReturn(7);

        $licenceEntity->shouldReceive('getOrganisation')
            ->twice()
            ->withNoArgs()
            ->andReturn($organisation);

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($licenceEntity);

        $organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn([]);

        $organisation->shouldReceive('getName')->once()->withNoArgs()->andReturn('SOME ORG');

        $expectedData = [
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
            'description' => 'Unable to send email - no organisation recipients found for Org: SOME ORG - Please update the organisation admin user contacts to ensure at least one has a valid email address.',
            'actionDate' => (new DateTime())->format('Y-m-d'),
        ];

        $this->expectedSideEffect(CreateTask::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['No email address available for the organisation'], $result->getMessages());
    }
}
