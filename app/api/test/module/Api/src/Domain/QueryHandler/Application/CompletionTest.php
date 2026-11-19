<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Completion;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Transfer\Query\Application\Completion as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

final class CompletionTest extends QueryHandlerTestCase
{
    private m\MockInterface $toggleService;

    private bool $isExternal = false;

    public function setUp(): void
    {
        $this->sut = new Completion();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->toggleService = m::mock(ToggleService::class);
        $this->mockedSmServices = [
            SmsNumberCapture::class => new SmsNumberCapture($this->toggleService),
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->andReturnUsing(fn ($permission) => $permission === Permission::SELFSERVE_USER && $this->isExternal);

        parent::setUp();
    }

    #[DataProvider('dpCompletion')]
    public function testHandleQuery(bool $isExternal, bool $captureOn, ?string $smsNumber, int $expectedAddressesStatus): void
    {
        $this->isExternal = $isExternal;
        $this->toggleService->shouldReceive('isEnabled')->with(FeatureToggle::SMS_NUMBER_CAPTURE)->andReturn($captureOn);

        $query = Qry::create(['id' => 7]);

        $correspondenceCd = m::mock(ContactDetails::class);
        $correspondenceCd->shouldReceive('getPhoneContacts')->andReturn(new \Doctrine\Common\Collections\ArrayCollection($smsNumber === null ? [] : [(new \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact(new \Dvsa\Olcs\Api\Entity\System\RefData(\Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact::TYPE_SMS)))->setPhoneNumber($smsNumber)]));
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($correspondenceCd);
        $completion = m::mock(ApplicationCompletion::class);
        $completion->shouldReceive('getAddressesStatus')->andReturn(ApplicationCompletion::STATUS_COMPLETE);

        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('isVariation')->andReturn(false);
        $application->shouldReceive('getStatus')->andReturn(new RefData(ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED));
        $application->shouldReceive('getApplicationCompletion')->andReturn($completion);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('serialize')->with(['applicationCompletion'])->andReturn([
            'id' => 7,
            'applicationCompletion' => ['addressesStatus' => ApplicationCompletion::STATUS_COMPLETE, 'peopleStatus' => 2],
        ]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $data = $this->sut->handleQuery($query)->serialize();

        $this->assertSame(7, $data['id']);
        $this->assertSame($expectedAddressesStatus, $data['applicationCompletion']['addressesStatus']);
        $this->assertSame(2, $data['applicationCompletion']['peopleStatus']);
    }

    public static function dpCompletion(): \Iterator
    {
        yield 'selfserve, capture on, no number' => [true, true, null, ApplicationCompletion::STATUS_INCOMPLETE];
        yield 'selfserve, has number' => [true, true, '+447700900123', ApplicationCompletion::STATUS_COMPLETE];
        yield 'capture off' => [true, false, null, ApplicationCompletion::STATUS_COMPLETE];
        yield 'internal user' => [false, true, null, ApplicationCompletion::STATUS_COMPLETE];
    }
}
