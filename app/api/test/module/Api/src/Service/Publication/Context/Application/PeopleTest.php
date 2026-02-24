<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PeopleTest extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Context\Application\People
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Context\Application\People(
            m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class)
        );

        parent::setUp();
    }

    public function testProvide(): void
    {
        $publicationLink = $this->getPublicationLink();
        $context = new \ArrayObject();

        $person432 = $this->getPerson(432, 'Tom', 'Jones');
        $person12 = $this->getPerson(12, 'Maggy', 'Smith');
        $person12Updated = $this->getPerson(12, 'Maggy_UP', 'Smith_UP');
        $person64 = $this->getPerson(64, 'John', 'Sheriden');
        $person72 = $this->getPerson(72, 'Carlton', 'Palmer');

        // not changed
        $this->addOrganisationPerson($publicationLink, $person432);
        // updated
        $this->addOrganisationPerson($publicationLink, $person12);
        $this->addApplicationOrganisationPerson($publicationLink, $person12Updated, 'U', $person12);
        // delete
        $this->addOrganisationPerson($publicationLink, $person64);
        $this->addApplicationOrganisationPerson($publicationLink, $person64, 'D');

        // added to app
        $this->addApplicationOrganisationPerson($publicationLink, $person72, 'A');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'applicationPeople' => [
                    432 => $person432,
                    12 => $person12Updated,
                    72 => $person72,
                ]
            ],
            $context->getArrayCopy()
        );
    }

    /**
     * @return PublicationLink
     */
    private function getPublicationLink(): mixed
    {
        $publicationLink = new PublicationLink();

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::VAR_NEW_SECTION);
        $publicationLink->setPublicationSection($publicationSection);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORG_NAME');

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new RefData());
        $publicationLink->setLicence($licence);

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), true);
        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    private function addOrganisationPerson(PublicationLink $publicationLink, Person $person): void
    {
        $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $organisationPerson->setPerson($person);
        $publicationLink->getLicence()->getOrganisation()->addOrganisationPersons($organisationPerson);
    }

    /**
     * @param string $action
     * @param Person $originalPerson
     */
    private function addApplicationOrganisationPerson(
        PublicationLink $publicationLink,
        Person $person,
        string $action = 'A',
        mixed $originalPerson = null
    ): void {
        $aop = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $publicationLink->getApplication(),
            $publicationLink->getLicence()->getOrganisation(),
            $person
        );
        $aop->setAction($action);
        if ($originalPerson) {
            $aop->setOriginalPerson($originalPerson);
        }

        $publicationLink->getApplication()->addApplicationOrganisationPersons($aop);
    }

    /**
     * @param string $forename
     * @param string $familyName
     *
     * @return \Dvsa\Olcs\Api\Entity\Person\Person
     */
    private function getPerson(mixed $id, mixed $forename, mixed $familyName): mixed
    {
        $person = new Person();
        $person->setId($id);
        $person->setForename($forename);
        $person->setFamilyName($familyName);

        return $person;
    }
}
