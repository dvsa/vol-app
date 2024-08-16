<?php

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

/**
 * Update Addresses Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateAddressesStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'Addresses';

    public function setUp(): void
    {
        $this->sut = new UpdateAddressesStatus();
        $this->command = Cmd::create(['id' => 111]);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoCorAddWithChange()
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoCorAddWithoutChange()
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoPhoneContactsExternal()
    {
        $this->setupIsExternalUser(true);

        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts(new ArrayCollection());

        $this->licence->setCorrespondenceCd($corAdd);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoPhoneContactsInternal()
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

    public function testHandleCommandNoEstAddWhenRequired()
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $phoneContacts = new ArrayCollection();
        $phoneContacts->add(m::mock(PhoneContact::class));

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts($phoneContacts);

        $this->licence->setCorrespondenceCd($corAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithEstAddWhenRequired()
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $phoneContacts = new ArrayCollection();
        $phoneContacts->add(m::mock(PhoneContact::class));

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts($phoneContacts);

        /** @var ContactDetailsEntity $corAdd */
        $estAdd = m::mock(ContactDetailsEntity::class)->makePartial();

        $this->licence->setCorrespondenceCd($corAdd);
        $this->licence->setEstablishmentCd($estAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutReqEstAdd()
    {
        $this->applicationCompletion->setAddressesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $phoneContacts = new ArrayCollection();
        $phoneContacts->add(m::mock(PhoneContact::class));

        /** @var ContactDetailsEntity $corAdd */
        $corAdd = m::mock(ContactDetailsEntity::class)->makePartial();
        $corAdd->setPhoneContacts($phoneContacts);

        $this->licence->setCorrespondenceCd($corAdd);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
