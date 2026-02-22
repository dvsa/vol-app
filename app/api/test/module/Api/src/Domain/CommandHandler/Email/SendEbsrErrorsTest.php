<?php

declare(strict_types=1);

/**
 * Send Ebsr Errors Email Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrErrors;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrErrors as Cmd;

/**
 * Send Ebsr Errors Test
 *
 * Ebsr Error emails follow a different code path if there is no bus reg created. This class tests this behaviour.
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrErrorsTest extends AbstractCommandHandlerTestCase
{
    protected $template = ['ebsr-data-error-start', 'ebsr-data-error-list', 'ebsr-data-error-end'];

    /**
     * @var CommandInterface
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendEbsrErrors();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    /**
     *
     * @param string $orgEmail
     * @param string $adminEmail
     * @param string $expectedToAddress
     * @param array $templateVars
     * @param string $subject
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $orgEmail, mixed $adminEmail, mixed $expectedToAddress, mixed $templateVars, mixed $subject): void
    {
        $ebsrSubmissionId = 1234;
        $orgAdminEmails = [0 => $adminEmail];
        $errors = ['errors'];
        $submissionResult = [
            'errors' => $errors,
            'extra_bus_data' => $templateVars
        ];

        $baseTemplateVars = [
            'submissionErrors' => $errors,
        ];

        $expectedTemplateVars = array_merge($baseTemplateVars, $templateVars);

        $command = Cmd::create(['id' => $ebsrSubmissionId]);

        $busRegEntity = null; //we're testing what happens when we don't have a bus reg

        $ebsrSubmissionEntity = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmissionEntity->shouldReceive('getId')->andReturn($ebsrSubmissionId);
        $ebsrSubmissionEntity->shouldReceive('getOrganisationEmailAddress')->once()->andReturn($orgEmail);
        $ebsrSubmissionEntity->shouldReceive('getBusReg')->once()->andReturn($busRegEntity);
        $ebsrSubmissionEntity->shouldReceive('getOrganisation->getAdminEmailAddresses')->andReturn($orgAdminEmails);
        $ebsrSubmissionEntity->shouldReceive('getDecodedSubmissionResult')->andReturn($submissionResult);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchUsingId')
            ->with(m::type(CommandInterface::class), Query::HYDRATE_OBJECT, null)
            ->once()
            ->andReturn($ebsrSubmissionEntity);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            $this->template,
            $expectedTemplateVars,
            'default'
        );

        $result = new Result();
        $data = [
            'to' => $expectedToAddress,
            'locale' => 'en_GB',
            'subject' => $subject
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['ebsrSubmission' => $ebsrSubmissionId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());
    }

    /**
     * Data provider for handleCommand
     *
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        $templateData = [
            'registrationNumber' => SendEbsrErrors::UNKNOWN_REG_NO
        ];

        $templateDataRegNo = [
            'registrationNumber' => 'OB12345657/8910'
        ];

        $subjectBus = 'email.ebsr-failed.subject';
        $subjectNoBus = 'email.ebsr-failed-no-bus-reg.subject';

        return [
            ['test@test.com', 'foo@bar.com', 'test@test.com', $templateData, $subjectNoBus],
            ['',  'foo@bar.com', 'foo@bar.com', $templateData, $subjectNoBus],
            ['test@test.com', 'foo@bar.com', 'test@test.com', $templateDataRegNo, $subjectBus],
            ['',  'foo@bar.com', 'foo@bar.com', $templateDataRegNo, $subjectBus]
        ];
    }
}
