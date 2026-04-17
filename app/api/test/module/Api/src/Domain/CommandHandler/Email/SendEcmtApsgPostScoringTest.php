<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApsgPostScoring as SendEcmtApsgPostScoringCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtApsgPostScoring as SendEcmtApsgPostScoringHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;

/**
 * Test the short term permit app part successful email
 */
class SendEcmtApsgPostScoringTest extends AbstractPermitTestCase
{
    public $orgEmails;
    public $contactDetails;
    public $userEmail;
    public $orgEmail1;
    public $orgEmail2;
    protected $commandClass = SendEcmtApsgPostScoringCmd::class;
    protected $commandHandlerClass = SendEcmtApsgPostScoringHandler::class;
    protected $template = 'ecmt-apsg-post-scoring-notification';
    protected $subject = 'email.ecmt.post.scoring.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTranslateToWelshLocaleMappings')]
    public function testHandleCommand(mixed $translateToWelsh, mixed $expectedLocale): void
    {
        $previousLocale = 'aa_DJ';
        $periodNameKey = 'period.name.translation.key';
        $periodName = 'Permits for journeys in January and Feburary 2020';

        $euro5PermitsRequired = 10;
        $euro6PermitsRequired = 11;
        $euro5PermitsGranted = 3;
        $euro6PermitsGranted = 8;

        $templateVars = [
            'applicationRef' => $this->applicationRef,
            'euro5PermitsRequired' => $euro5PermitsRequired,
            'euro6PermitsRequired' => $euro6PermitsRequired,
            'euro5PermitsGranted' => $euro5PermitsGranted,
            'euro6PermitsGranted' => $euro6PermitsGranted,
            'periodName' => 'Permits for journeys in January and Feburary 2020',
        ];

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitsGranted);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitsGranted);

        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($this->applicationEntity)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);
        $this->applicationEntity->shouldReceive('getAssociatedStock->getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($periodNameKey);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->contactDetails->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($this->userEmail);
        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(false);
        $this->userEntity->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($this->contactDetails);

        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($euro5PermitsRequired);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($euro6PermitsRequired);

        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->andReturn($previousLocale)
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('translate')
            ->with($periodNameKey)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($periodName);
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')
            ->withNoArgs()
            ->andReturn($translateToWelsh);

        $expectedData = [
            'to' => $this->userEmail,
            'locale' => $expectedLocale,
            'subject' => $this->subject,
        ];

        $this->expectedSideEffect(SendEmail::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($this->commandEntity);

        $this->assertSame(['IrhpApplication' => $this->permitAppId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame($this->userEmail, $message->getTo());
        $this->assertSame($this->orgEmails, $message->getCc());
        $this->assertSame($this->subject, $message->getSubject());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTranslateToWelshLocaleMappings')]
    public function testHandleCommandForCreatedByInternalUser(mixed $translateToWelsh, mixed $expectedLocale): void
    {
        $previousLocale = 'aa_DJ';
        $periodNameKey = 'period.name.translation.key';
        $periodName = 'Permits for journeys in January and Feburary 2020';

        $euro5PermitsRequired = 10;
        $euro6PermitsRequired = 11;
        $euro5PermitsGranted = 3;
        $euro6PermitsGranted = 8;

        $templateVars = [
            'applicationRef' => $this->applicationRef,
            'euro5PermitsRequired' => $euro5PermitsRequired,
            'euro6PermitsRequired' => $euro6PermitsRequired,
            'euro5PermitsGranted' => $euro5PermitsGranted,
            'euro6PermitsGranted' => $euro6PermitsGranted,
            'periodName' => 'Permits for journeys in January and Feburary 2020',
        ];

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitsGranted);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitsGranted);

        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($this->applicationEntity)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(true);
        $this->userEntity->shouldReceive('getContactDetails')->never();

        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($euro5PermitsRequired);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($euro6PermitsRequired);

        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->andReturn($previousLocale)
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('translate')
            ->with($periodNameKey)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($periodName);
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')
            ->withNoArgs()
            ->andReturn($translateToWelsh);
        $this->applicationEntity->shouldReceive('getAssociatedStock->getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($periodNameKey);

        $expectedData = [
            'to' => $this->orgEmail1,
            'locale' => $expectedLocale,
            'subject' => $this->subject,
        ];

        $this->expectedSideEffect(SendEmail::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($this->commandEntity);

        $this->assertSame(['IrhpApplication' => $this->permitAppId], $result->getIds());
        $this->assertSame(['Email sent'], $result->getMessages());

        /** @var Message $message */
        $message = $this->sut->getMessage();
        $this->assertSame($this->orgEmail1, $message->getTo());
        $this->assertSame([1 => $this->orgEmail2], $message->getCc());
        $this->assertSame($this->subject, $message->getSubject());
    }

    public static function dpTranslateToWelshLocaleMappings(): array
    {
        return [
            ['Y', 'cy_GB'],
            ['N', 'en_GB'],
        ];
    }
}
