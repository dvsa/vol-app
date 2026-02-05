<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;

/**
 * Abstract ECMT annual permit email tester
 */
abstract class AbstractEcmtAnnualPermitTestCase extends AbstractPermitTestCase
{
    public $orgEmails;
    public $contactDetails;
    public $userEmail;
    public $orgEmail1;
    public $orgEmail2;
    /**
     * test handle command
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpLocaleMappings')]
    public function testHandleCommand(mixed $licenceTranslateToWelsh, mixed $expectedLocale): void
    {
        $paymentDeadlineNumDays = '10';

        $templateVars = [
            'appUrl' => 'http://selfserve/',
            'permitsUrl' => 'http://selfserve/permits',
            'guidanceUrl' => 'https://www.gov.uk/guidance/international-authorisations-and-permits-for-road-haulage',
            'ecmtGuidanceUrl' => 'https://www.gov.uk/guidance/ecmt-international-road-haulage-permits',
            'applicationRef' => $this->applicationRef,
            'applicationFee' => '10',
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $this->applicationEntity->shouldReceive('isAwaitingFee')->once()->withNoArgs()->andReturn(false);
        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')->andReturn($licenceTranslateToWelsh);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $applicationFee = '10.00';

        $feeTypeEntity = m::mock(FeeType::class);
        $feeTypeEntity->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn($applicationFee);
        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->once()
            ->with(FeeType::FEE_TYPE_ECMT_APP_PRODUCT_REF)
            ->andReturn($feeTypeEntity);

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

    /**
     * test handle command when the application created by internal user
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpLocaleMappings')]
    public function testHandleCommandForCreatedByInternalUser(mixed $licenceTranslateToWelsh, mixed $expectedLocale): void
    {
        $paymentDeadlineNumDays = '10';

        $templateVars = [
            'appUrl' => 'http://selfserve/',
            'permitsUrl' => 'http://selfserve/permits',
            'guidanceUrl' => 'https://www.gov.uk/guidance/international-authorisations-and-permits-for-road-haulage',
            'ecmtGuidanceUrl' => 'https://www.gov.uk/guidance/ecmt-international-road-haulage-permits',
            'applicationRef' => $this->applicationRef,
            'applicationFee' => '10',
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $this->applicationEntity->shouldReceive('isAwaitingFee')->once()->withNoArgs()->andReturn(false);
        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')->andReturn($licenceTranslateToWelsh);

        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->andReturn($this->orgEmails);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $applicationFee = '10.00';

        $feeTypeEntity = m::mock(FeeType::class);
        $feeTypeEntity->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn($applicationFee);
        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->once()
            ->with(FeeType::FEE_TYPE_ECMT_APP_PRODUCT_REF)
            ->andReturn($feeTypeEntity);

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

    /**
     * test handle command awaiting fee
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpLocaleMappings')]
    public function testHandleCommandAwaitingFee(mixed $licenceTranslateToWelsh, mixed $expectedLocale): void
    {
        $permitsRequired = 8;
        $permitsAwarded = 6;
        $euro5PermitsRequired = 3;
        $euro5PermitsAwarded = 2;
        $euro6PermitsRequired = 5;
        $euro6PermitsAwarded = 4;
        $issueFeeAmount = 123;
        $permitsGrantedissueFeeTotal = $permitsRequired * $issueFeeAmount;
        $paymentDeadlineNumDays = '10';
        $issueFeeDeadlineDate = '21 March 2019';
        $awaitingFeeUrl = 'http://selfserve/permits/application/' . $this->permitAppId . '/awaiting-fee';
        $validityYear = '2022';

        $templateVars = [
            'appUrl' => 'http://selfserve/',
            'permitsUrl' => 'http://selfserve/permits',
            'guidanceUrl' => 'https://www.gov.uk/guidance/international-authorisations-and-permits-for-road-haulage',
            'ecmtGuidanceUrl' => 'https://www.gov.uk/guidance/ecmt-international-road-haulage-permits',
            'applicationRef' => $this->applicationRef,
            'awaitingFeeUrl' => $awaitingFeeUrl,
            'permitsRequired' => $permitsRequired,
            'permitsGranted' => $permitsAwarded,
            'euro5PermitsRequired' => $euro5PermitsRequired,
            'euro6PermitsRequired' => $euro6PermitsRequired,
            'euro5PermitsGranted' => $euro5PermitsAwarded,
            'euro6PermitsGranted' => $euro6PermitsAwarded,
            'paymentDeadlineNumDays' => $paymentDeadlineNumDays,
            'issueFeeDeadlineDate' => $issueFeeDeadlineDate,
            'issueFeeAmount' => $issueFeeAmount,
            'issueFeeTotal' => $permitsGrantedissueFeeTotal,
            'applicationFee' => '10',
            'validityYear' => $validityYear,
        ];

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->once()
            ->withNoArgs()
            ->andReturn($paymentDeadlineNumDays);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('calculateTotalPermitsRequired')
            ->withNoArgs()
            ->andReturn($permitsRequired);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->withNoArgs()
            ->andReturn($permitsAwarded);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($euro5PermitsRequired);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitsAwarded);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($euro6PermitsRequired);
        $irhpPermitApplication->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitsAwarded);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getValidityYear')
            ->withNoArgs()
            ->andReturn($validityYear);

        $fee = m::mock(Fee::class);
        $fee->shouldReceive('isEcmtIssuingFee')->withNoArgs()->andReturn(true);
        $fee->shouldReceive('getFeeTypeAmount')->withNoArgs()->andReturn($issueFeeAmount);
        $fee->shouldReceive('getOutstandingAmount')->withNoArgs()->andReturn($permitsGrantedissueFeeTotal);
        $fee->shouldReceive('getInvoicedDateTime')->withNoArgs()->andReturn(
            new DateTime('8 March 2019')
        );
        $fees = [$fee];

        $this->applicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $this->applicationEntity->shouldReceive('calculateTotalPermitsRequired')->andReturn($permitsRequired);
        $this->applicationEntity->shouldReceive('getFees->matching')->andReturn($fees);
        $this->applicationEntity->shouldReceive('isAwaitingFee')->once()->withNoArgs()->andReturn(true);
        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn($this->userEntity);
        $this->applicationEntity->shouldReceive('getLicence->getTranslateToWelsh')
            ->withNoArgs()
            ->andReturn($licenceTranslateToWelsh);

        $this->organisation->shouldReceive('getAdminEmailAddresses')
            ->once()
            ->withNoArgs()
            ->andReturn($this->orgEmails);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            $this->template,
            $templateVars,
            'default'
        );

        $applicationFee = '10.00';

        $feeTypeEntity = m::mock(FeeType::class);
        $feeTypeEntity->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn($applicationFee);
        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->once()
            ->with(FeeType::FEE_TYPE_ECMT_APP_PRODUCT_REF)
            ->andReturn($feeTypeEntity);

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

    public static function dpLocaleMappings(): array
    {
        return [
            ['N', 'en_GB'],
            ['Y', 'cy_GB']
        ];
    }

    /**
     * test the exception is dealt with when there are no email addresses
     */
    public function testHandleCommandException(): void
    {
        $this->organisation->shouldReceive('getAdminEmailAddresses')->once()->withNoArgs()->andReturn([]);
        $this->applicationEntity->shouldReceive('getCreatedBy')->once()->withNoArgs()->andReturn(null);

        $this->organisation->shouldReceive('getName')->once()->withNoArgs()->andReturn('SOME ORG');

        $this->applicationEntity->shouldReceive('getLicence->getId')->once()->withNoArgs()->andReturn(7);

        $expectedData = [
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
            'description' => 'Unable to send email - no organisation recipients found for Org: SOME ORG - Please update the organisation admin user contacts to ensure at least one has a valid email address.',
            'actionDate' => (new DateTime())->format('Y-m-d'),
        ];

        $this->expectedSideEffect(CreateTask::class, $expectedData, new Result());

        $result = $this->sut->handleCommand($this->commandEntity);

        $this->assertSame(['IrhpApplication' => $this->permitAppId], $result->getIds());
        $this->assertSame([MissingEmailException::MSG_NO_ORG_EMAIL], $result->getMessages());
        $this->assertNull($this->sut->getMessage());
    }
}
