<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Person;

use Common\Data\Object\Bundle\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Person\Person as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;

/**
 * Person Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PersonEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('birthDateProvider')]
    public function testUpdatePerson(mixed $birthDate, mixed $expected): void
    {
        $entity = new Entity();
        $title = m::mock(RefData::class);

        $entity->updatePerson('forename', 'familyname', $title, $birthDate, 'bplace');

        $this->assertSame($title, $entity->getTitle());
        $this->assertEquals('forename', $entity->getForename());
        $this->assertEquals('familyname', $entity->getFamilyName());
        $this->assertEquals($expected, $entity->getBirthDate());
        $this->assertEquals('bplace', $entity->getBirthPlace());
    }

    public static function birthDateProvider(): array
    {
        return [
            ['2015-01-01', new \DateTime('2015-01-01')],
            ['', null]
        ];
    }

    public function testGetContactDetail(): void
    {
        $contactDetails = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new RefData());

        $person = new Entity();
        $person->addContactDetails($contactDetails);

        $this->assertSame($contactDetails, $person->getContactDetail());
    }

    public function testGetCalculatedValues(): void
    {
        $person = m::mock(Entity::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $person->shouldReceive('getDisqualificationStatus')->with()->once()->andReturn('STATUS');

        $this->assertSame(['disqualificationStatus' => 'STATUS'], $person->getCalculatedBundleValues());
    }

    public function testGetFullName(): void
    {
        $person = new Entity();
        $person->setForename('Foo');
        $person->setFamilyName('Bar');

        $this->assertSame('Foo Bar', $person->getFullName());
    }

    public function testGetDisqualificationNull(): void
    {
        /* @var $organisation Entity */
        $person = $this->instantiate($this->entityClass);
        $person->setDisqualifications(new \Doctrine\Common\Collections\ArrayCollection());

        $this->assertSame(null, $person->getDisqualification());
    }

    public function testGetDisqualification(): void
    {
        $disqualification = new Disqualification(m::mock(Organisation::class));

        /* @var $person Entity */
        $person = $this->instantiate($this->entityClass);
        $person->setDisqualifications(new \Doctrine\Common\Collections\ArrayCollection([$disqualification]));

        $this->assertSame($disqualification, $person->getDisqualification());
    }

    public function testGetDisqualificationStatusNone(): void
    {
        /* @var $person Entity */
        $person = $this->instantiate($this->entityClass);
        $person->setDisqualifications(new \Doctrine\Common\Collections\ArrayCollection());

        $this->assertSame(Disqualification::STATUS_NONE, $person->getDisqualificationStatus());
    }

    public function testGetDisqualificationStatusActive(): void
    {
        $disqualification = new Disqualification(m::mock(Organisation::class));
        $disqualification->setIsDisqualified('Y');
        $disqualification->setStartDate('2015-01-01');

        /* @var $person Entity */
        $person = $this->instantiate($this->entityClass);
        $person->setDisqualifications(new \Doctrine\Common\Collections\ArrayCollection([$disqualification]));

        $this->assertSame(Disqualification::STATUS_ACTIVE, $person->getDisqualificationStatus());
    }

    public function testGetRelatedOrganisation(): void
    {
        $person = $this->instantiate($this->entityClass);
        $op = new OrganisationPerson();
        $org1 = m::mock(Organisation::class)->makePartial()->setId(1);
        $org2 = m::mock(Organisation::class)->makePartial()->setId(2);
        $app = m::mock(ApplicationEntity::class);
        $op->setOrganisation($org1);
        $op->setPerson($person);
        $aop = m::mock(ApplicationOrganisationPerson::class)->makePartial();
        $aop->setApplication($app);
        $aop->setOrganisation($org2);
        $aop->setPerson($person);

        $person->setOrganisationPersons([$op]);
        $person->setApplicationOrganisationPersons([$aop]);

        $expected = [
            1 => $org1,
            2 => $org2
        ];

        $this->assertEquals($expected, $person->getRelatedOrganisation());
    }
}
