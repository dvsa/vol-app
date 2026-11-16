<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Application;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Transfer\Query\Application\Application as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\OlcsTest\Api\Entity\User as UserEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ApplicationTest extends QueryHandlerTestCase
{
    private m\MockInterface $toggleService;

    private bool $isExternal = false;

    public function setUp(): void
    {
        $this->sut = new Application();

        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Note', NoteRepo::class);
        $this->mockRepo('SystemParameter', \Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);

        $this->toggleService = m::mock(ToggleService::class);
        $this->toggleService->shouldReceive('isEnabled')->andReturn(false)->byDefault();

        $this->mockedSmServices = [
            'FeesHelperService' => m::mock(),
            'SectionAccessService' => m::mock(),
            AuthorizationService::class => m::mock(AuthorizationService::class),
            SmsNumberCapture::class => new SmsNumberCapture($this->toggleService),
        ];
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->andReturnUsing(fn ($permission) => $permission === Permission::SELFSERVE_USER && $this->isExternal);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicationId = 111;

        $query = Qry::create(['id' => $applicationId, 'validateAppCompletion' => true]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application
            ->setId($applicationId)
            ->shouldReceive('getPublicationLinks')->with()->once()
                ->andReturn(new \Doctrine\Common\Collections\ArrayCollection())
            ->shouldReceive('getVariationCompletion')
            ->andReturn(null)
            ->once()
            ->shouldReceive('isPublishable')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(222)
                ->once()
                ->shouldReceive('getOrganisation')->andReturn(
                    m::mock(Organisation::class)
                        ->shouldReceive('isMlh')
                        ->once()
                        ->andReturn(true)
                        ->shouldReceive('getAllowedOperatorLocation')
                        ->once()
                        ->andReturn('GB')
                        ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('isSpecialRestricted')
            ->andReturn(false)
            ->once()
            ->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $application->setStatus(new \Dvsa\Olcs\Api\Entity\System\RefData()->setId('apsts_not_submitted'));
        $application->setIsVariation(true);

        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->with()->once()
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->repoMap['Note']->shouldReceive('fetchForOverview')
            ->with(222)
            ->andReturn('latest note');

        $sections = ['bar', 'cake'];

        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSections')
            ->once()
            ->with($application)
            ->andReturn($sections);

        $applicationFee = $this->getMockFee('100');
        $interimFee =  $this->getMockFee('66.70');
        $fees = [$applicationFee, $interimFee];
        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with($applicationId)
            ->once()
            ->andReturn(166.70);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(UpdateApplicationCompletionCmd::class)
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'sections' => ['bar', 'cake'],
            'outstandingFeeTotal' => '166.70',
            'variationCompletion' => null,
            'canCreateCase' => false,
            'existingPublication' => false,
            'isPublishable' => true,
            'latestNote' => 'latest note',
            'disableCardPayments' => false,
            'isMlh' => true,
            'allowedOperatorLocation' => 'GB',
            'canHaveInspectionRequest' => true,
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpStaleAddressesCompletion')]
    public function testStaleCompleteAddressesShownIncompleteWhenSmsNumberMissing(
        bool $isExternal,
        bool $captureOn,
        ?string $smsNumber,
        string $status,
        int $expectedAddressesStatus
    ): void {
        $this->isExternal = $isExternal;
        $this->toggleService->shouldReceive('isEnabled')
            ->with(FeatureToggle::SMS_NUMBER_CAPTURE)
            ->andReturn($captureOn);

        $query = Qry::create(['id' => 7, 'validateAppCompletion' => true]);

        $correspondenceCd = m::mock(ContactDetails::class);
        $correspondenceCd->shouldReceive('getPhoneContacts')->andReturn(new \Doctrine\Common\Collections\ArrayCollection($smsNumber === null ? [] : [(new \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact(new \Dvsa\Olcs\Api\Entity\System\RefData(\Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact::TYPE_SMS)))->setPhoneNumber($smsNumber)]));

        $organisation = m::mock(OrganisationEntity::class);
        $organisation->shouldReceive('isMlh')->andReturn(false);
        $organisation->shouldReceive('getAllowedOperatorLocation')->andReturn('GB');

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getId')->andReturn(211);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($correspondenceCd);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);

        $completion = m::mock(ApplicationCompletion::class);
        $completion->shouldReceive('getAddressesStatus')->andReturn(ApplicationCompletion::STATUS_COMPLETE);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(7);
        $application->setIsVariation(false);
        $application->setStatus((new RefData())->setId($status));
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('getApplicationCompletion')->andReturn($completion);
        $application->shouldReceive('getPublicationLinks')->andReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $application->shouldReceive('getVariationCompletion')->andReturn(null);
        $application->shouldReceive('isPublishable')->andReturn(false);
        $application->shouldReceive('canCreateCase')->andReturn(false);
        $application->shouldReceive('isSpecialRestricted')->andReturn(false);
        $application->shouldReceive('serialize')->andReturn([
            'applicationCompletion' => ['addressesStatus' => ApplicationCompletion::STATUS_COMPLETE, 'peopleStatus' => 2],
        ]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')->andReturn(null);
        $this->repoMap['SystemParameter']->shouldReceive('getDisableSelfServeCardPayments')->andReturn(false);
        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSections')->andReturn([]);
        $this->mockedSmServices['FeesHelperService']->shouldReceive('getTotalOutstandingFeeAmountForApplication')->andReturn(0);
        // a query, so nothing is written
        $this->commandHandler->shouldReceive('handleCommand')->never();

        $data = $this->sut->handleQuery($query)->serialize();

        $this->assertSame($expectedAddressesStatus, $data['applicationCompletion']['addressesStatus']);
        $this->assertSame(2, $data['applicationCompletion']['peopleStatus']);
    }

    public static function dpStaleAddressesCompletion(): \Iterator
    {
        $notSubmitted = ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED;
        $complete = ApplicationCompletion::STATUS_COMPLETE;
        $incomplete = ApplicationCompletion::STATUS_INCOMPLETE;

        yield 'selfserve, capture on, no number' => [true, true, null, $notSubmitted, $incomplete];
        yield 'selfserve, capture on, has number' => [true, true, '+447700900123', $notSubmitted, $complete];
        yield 'selfserve, capture off' => [true, false, null, $notSubmitted, $complete];
        yield 'internal user' => [false, true, null, $notSubmitted, $complete];
        yield 'already submitted' => [true, true, null, ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION, $complete];
    }

    protected function getMockFee(mixed $amount): mixed
    {
        $mock = m::mock(FeeEntity::class)->makePartial();
        $mock
            ->setGrossAmount($amount)
            ->shouldReceive('getOutstandingAmount')
            ->andReturn($amount)
            ->shouldReceive('serialize')
            ->andReturn(['amount' => $amount]);
        return $mock;
    }
}
