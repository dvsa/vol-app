<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermSuccessful as SendEcmtShortTermSuccessfulCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEcmtShortTermSuccessful as SendEcmtShortTermSuccessfulHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;

/**
 * Test the short term permit app successful email
 */
class SendEcmtShortTermSuccessfulTest extends AbstractPermitTestCase
{
    public $orgEmails;
    public $contactDetails;
    public $userEmail;
    public $orgEmail1;
    public $orgEmail2;
    protected $commandClass = SendEcmtShortTermSuccessfulCmd::class;
    protected $commandHandlerClass = SendEcmtShortTermSuccessfulHandler::class;
    protected $template = 'ecmt-short-term-app-successful';
    protected $subject = 'email.ecmt.short.term.response.subject';
    protected $permitApplicationRepo = 'IrhpApplication';
    protected $applicationEntityClass = IrhpApplication::class;

    private $previousLocale = 'aa_DJ';
    private $periodNameKey = 'period.name.translation.key';
    private $periodName = 'Permits for journeys in January and Feburary 2020';

    private $euro5PermitsGranted = 3;
    private $euro6PermitsGranted = 8;

    private $issueFeeAmount = '10.00';
    private $issueFeeAmountFormatted = '10';
    private $issueFeeTotal = '110.00';
    private $issueFeeTotalFormatted = '110';

    private $irhpPermitStock;
    private $irhpPermitApplication;

    public function setUp(): void
    {
        parent::setUp();

        $paymentDeadlineNumDays = 10;

        $issueFee = m::mock(Fee::class);
        $issueFee->shouldReceive('getFeeTypeAmount')
            ->andReturn($this->issueFeeAmount);
        $issueFee->shouldReceive('getOutstandingAmount')
            ->andReturn($this->issueFeeTotal);
        $issueFee->shouldReceive('getInvoicedDateTime')
            ->andReturn(new DateTime('8 March 2019'));

        $templateVars = [
            'applicationRef' => $this->applicationRef,
            'euro5PermitsGranted' => $this->euro5PermitsGranted,
            'euro6PermitsGranted' => $this->euro6PermitsGranted,
            'issueFeeAmount' => $this->issueFeeAmountFormatted,
            'issueFeeTotal' => $this->issueFeeTotalFormatted,
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
            'issueFeeDeadlineDate' => '21 March 2019',
            'paymentUrl' => 'http://selfserve/permits/application/' . $this->permitAppId . '/awaiting-fee',
            'periodName' => 'Permits for journeys in January and Feburary 2020',
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $this->irhpPermitStock = m::mock(IrhpPermitStock::class);
        $this->irhpPermitStock->shouldReceive('getPeriodNameKey')
            ->andReturn($this->periodNameKey);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->andReturn($this->irhpPermitStock);

        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($this->applicationEntity)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($this->irhpPermitApplication);
        $this->applicationEntity->shouldReceive('getLatestIssueFee')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($issueFee);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTranslateToWelshLocaleMappings')]
    public function testHandleCommand(mixed $translateToWelsh, mixed $expectedLocale): void
    {
        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->andReturn($this->previousLocale)
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('translate')
            ->with($this->periodNameKey)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($this->periodName);
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($this->previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')
            ->withNoArgs()
            ->andReturn($translateToWelsh);
        $this->irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($this->euro5PermitsGranted);
        $this->irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($this->euro6PermitsGranted);

        $this->contactDetails->shouldReceive('getEmailAddress')->once()->withNoArgs()->andReturn($this->userEmail);
        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(false);
        $this->userEntity->shouldReceive('getContactDetails')->once()->withNoArgs()->andReturn($this->contactDetails);

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
        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->andReturn($this->previousLocale)
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();
        $this->mockedSmServices['translator']->shouldReceive('translate')
            ->with($this->periodNameKey)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($this->periodName);
        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($this->previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')
            ->withNoArgs()
            ->andReturn($translateToWelsh);
        $this->irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($this->euro5PermitsGranted);
        $this->irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($this->euro6PermitsGranted);

        $this->userEntity->shouldReceive('isInternal')->once()->withNoArgs()->andReturn(true);
        $this->userEntity->shouldReceive('getContactDetails')->never();

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
