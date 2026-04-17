<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
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
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Context\Licence\People(
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
        $person64 = $this->getPerson(64, 'John', 'Sheriden');

        $this->addOrganisationPerson($publicationLink, $person432);
        $this->addOrganisationPerson($publicationLink, $person12);
        $this->addOrganisationPerson($publicationLink, $person64);

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'licencePeople' => [
                    432 => $person432,
                    12 => $person12,
                    64 => $person64,
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

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new RefData());
        $publicationLink->setLicence($licence);

        return $publicationLink;
    }

    private function addOrganisationPerson(PublicationLink $publicationLink, Person $person): void
    {
        $organisationPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $organisationPerson->setPerson($person);
        $publicationLink->getLicence()->getOrganisation()->addOrganisationPersons($organisationPerson);
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
