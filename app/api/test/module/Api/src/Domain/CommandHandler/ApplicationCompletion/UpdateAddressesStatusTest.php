<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateAddressesStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateAddressesStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Update Addresses Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateAddressesStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'Addresses';

    private m\MockInterface $toggleService;

    public function setUp(): void
    {
        $this->sut = new UpdateAddressesStatus();
        $this->command = Cmd::create(['id' => 111]);

        $this->toggleService = m::mock(ToggleService::class);
        $this->toggleService->shouldReceive('isEnabled')
            ->with(FeatureToggle::SMS_NUMBER_CAPTURE)
            ->andReturn(true)
            ->byDefault();

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class),
            SmsNumberCapture::class => new SmsNumberCapture($this->toggleService),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoCorAddWithChange(): void
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoCorAddWithoutChange(): void
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoPhoneContactsExternal(): void
    {
        $this->setupIsExternalUser(true);

        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts(new ArrayCollection());

        $this->licence->setCorrespondenceCd($corAdd);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoPhoneContactsInternal(): void
    {
        $this->setupIsExternalUser(false);

        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts(new ArrayCollection());

        $this->licence->setCorrespondenceCd($corAdd);

        $this->licence->setEstablishmentCd(m::mock(ContactDetailsEntity::class)->makePartial());

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandNoEstAddWhenRequired(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $corAdd = $this->corAddWithPhones([PhoneContact::TYPE_PRIMARY, PhoneContact::TYPE_SMS]);

        $this->licence->setCorrespondenceCd($corAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithEstAddWhenRequired(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $corAdd = $this->corAddWithPhones([PhoneContact::TYPE_PRIMARY, PhoneContact::TYPE_SMS]);

        /** @var ContactDetailsEntity $corAdd */
        $estAdd = m::mock(ContactDetailsEntity::class)->makePartial();

        $this->licence->setCorrespondenceCd($corAdd);
        $this->licence->setEstablishmentCd($estAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutReqEstAdd(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $corAdd = $this->corAddWithPhones([PhoneContact::TYPE_PRIMARY, PhoneContact::TYPE_SMS]);

        $this->licence->setCorrespondenceCd($corAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    private function corAddWithPhones(array $types): ContactDetailsEntity
    {
        $phoneContacts = new ArrayCollection();
        foreach ($types as $type) {
            $pc = new PhoneContact(new \Dvsa\Olcs\Api\Entity\System\RefData($type));
            $pc->setPhoneNumber('07700900123');
            $phoneContacts->add($pc);
        }

        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts($phoneContacts);
        return $corAdd;
    }

    public function testExternalWithoutSmsIsIncomplete(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd($this->corAddWithPhones([PhoneContact::TYPE_PRIMARY]));
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testExternalWithoutSmsIsCompleteWhenCaptureOff(): void
    {
        $this->toggleService->shouldReceive('isEnabled')
            ->with(FeatureToggle::SMS_NUMBER_CAPTURE)
            ->andReturn(false);
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd($this->corAddWithPhones([PhoneContact::TYPE_PRIMARY]));
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testInternalWithoutSmsIsComplete(): void
    {
        $this->setupIsExternalUser(false);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd($this->corAddWithPhones([PhoneContact::TYPE_PRIMARY]));
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testExternalWithSmsIsComplete(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd(
            $this->corAddWithPhones([PhoneContact::TYPE_PRIMARY, PhoneContact::TYPE_SMS])
        );
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testExternalWithOnlyAnSmsNumberIsIncomplete(): void
    {
        $this->setupIsExternalUser(true);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd($this->corAddWithPhones([PhoneContact::TYPE_SMS]));
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testInternalWithOnlyAnSmsNumberIsComplete(): void
    {
        $this->setupIsExternalUser(false);
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setCorrespondenceCd($this->corAddWithPhones([PhoneContact::TYPE_SMS]));
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
