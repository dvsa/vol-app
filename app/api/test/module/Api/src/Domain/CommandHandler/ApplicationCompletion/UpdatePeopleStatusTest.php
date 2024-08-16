<?php

/**
 * Update People Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdatePeopleStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdatePeopleStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Update People Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdatePeopleStatusTest extends AbstractUpdateStatusTestCase
{
    /**
     * @var Organisation
     */
    protected $organisation;

    protected $section = 'People';

    public function setUp(): void
    {
        $this->sut = new UpdatePeopleStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();

        $this->organisation = m::mock(Organisation::class)->makePartial();
        $this->licence->setOrganisation($this->organisation);
        $this->application->setApplicationOrganisationPersons(new \Doctrine\Common\Collections\ArrayCollection());
    }

    public function testHandleCommandWithChange()
    {
        $this->organisation->setOrganisationPersons(new ArrayCollection());
        $this->applicationCompletion->setPeopleStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange()
    {
        $this->organisation->setOrganisationPersons(new ArrayCollection());
        $this->applicationCompletion->setPeopleStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandDeletedAop()
    {
        $this->applicationCompletion->setPeopleStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setOrganisationPersons(new ArrayCollection(['foo']));

        $aop1 = m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::class)->makePartial();
        $aop1->setAction('D');
        $this->application->setApplicationOrganisationPersons(
            new \Doctrine\Common\Collections\ArrayCollection([$aop1])
        );

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand()
    {
        $this->applicationCompletion->setPeopleStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setOrganisationPersons(['foo']);

        $aop1 = m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::class)->makePartial();
        $aop1->setAction('D');
        $aop2 = m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::class)->makePartial();
        $aop2->setAction('A');
        $this->application->setApplicationOrganisationPersons(
            new \Doctrine\Common\Collections\ArrayCollection([$aop1, $aop2])
        );

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
